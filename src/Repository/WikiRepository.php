<?php

namespace App\Repository;

use App\Entity\Memo;
use App\Entity\Wiki;
use App\MarkdownContent\MarkdownReader;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;

class WikiRepository
{
    private MarkdownReader $markdownReader;
    private CacheInterface $cache;
    private Environment $twig;
    private MemoRepository $memoRepo;

    public function __construct(
        CacheInterface $cache,
        MarkdownReader $markdownReader,
        string $path,
        Environment $twig,
        MemoRepository $memoRepository
    ) {
        $this->cache = $cache;
        $this->markdownReader = $markdownReader;
        $this->twig = $twig;
        $this->memoRepo = $memoRepository;
    }

    public function findAll(): array
    {
        return $this->cache->get(
            'all',
            $this->getAllEntries()
        );
    }

    public function findOneByPath(string $path): string
    {
        return $this->cache->get(
            \md5($path),
            $this->getEntry($path)
        );
    }

    private function getAllEntries(): \Closure
    {
        return function (ItemInterface $item) {
            $item->expiresAfter(10);
            $entries = $this->memoRepo->findAllWiki();
            $result = [];
            foreach ($entries as $entry) {
                $group = strtoupper(substr($entry['title'], 0, 1));
                $result[$group][] = $entry;
            }
            return $result;
        };
    }

    private function getEntry(string $path): \Closure
    {
        return function (ItemInterface $item) use ($path) {
            $item->expiresAfter(10);
            $memo = $this->memoRepo->findOneWiki($path);
            return $this->markdownReader->parseString($memo->getContent());
        };
    }

    public function save(Wiki $wiki): void
    {
        $memo = new Memo();
        $memo->setFileName($wiki->getName() . '.md')
            ->setTitle($wiki->getName())
            ->setContent($this->twig->render('memo/simple-template.html.twig', ['memo' => $wiki]))
            ->setLocation('/Wiki')
            ->setExtension('md')
            ->setOnDisk(false)
            ->setType('Wiki');
        $this->memoRepo->save($memo);

        $key = \md5($wiki->getName());
        $this->cache->delete($key);
        $this->cache->get(
            $key,
            $this->getEntry($memo->getFileName())
        );

        $this->cache->delete('all');
        $this->findAll();
    }

    public function findOneByPathRaw(string $path)
    {
        return $this->memoRepo->findOneWiki($path);
    }

    public function update(Wiki $wiki): void
    {
        $memo = $this->memoRepo->find($wiki->getId());
        $memo->setContent($wiki->getContent());
        $memo->setTitle($wiki->getName());
        $memo->setFileName($memo->getTitle() . '.md');
        $this->memoRepo->save($memo);
        $key = \md5($wiki->getName());
        $this->cache->delete($key);
        $this->cache->get(
            $key,
            $this->getEntry($memo->getFileName())
        );

        $this->cache->delete('all');
        $this->findAll();
    }
}
