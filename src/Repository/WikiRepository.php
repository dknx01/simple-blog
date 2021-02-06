<?php

namespace App\Repository;

use App\Entity\Wiki;
use App\MarkdownContent\MarkdownReader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;
use function Symfony\Component\String\u;

class WikiRepository
{
    private string $path;
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
        $this->path = $path . '/Wiki/';
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

    public function finOneRawByPath(string $path): string
    {
        return $this->getRawEntry($path);
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

    private function getRawEntry(string $path): string
    {
        return file_get_contents($this->path . $path . '.md');
    }

    public function save(Wiki $wiki): void
    {
        $filepath = sprintf(
            '%s/%s.md',
            $this->path,
            $wiki->getName()
        );
        $this->filesystem->dumpFile(
            $filepath,
            $this->twig->render('memo/simple-template.html.twig', ['memo' => $wiki])
        );
        $key = \md5($wiki->getName());
        $this->cache->delete($key);
        $this->cache->get(
            $key,
            $this->getEntry($wiki->getName())
        );

        $this->cache->delete('all');
        $this->findAll();
    }

    private function sort(): \Closure
    {
        return static function (\SplFileInfo $a, \SplFileInfo $b) {
            return strnatcasecmp($a->getRealPath() ?: $a->getPathname(), $b->getRealPath() ?: $b->getPathname());
        };
    }
}
