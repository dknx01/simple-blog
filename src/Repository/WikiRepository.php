<?php

namespace App\Repository;

use App\Entity\Wiki;
use App\MarkdownContent\MarkdownReader;
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

    public function __construct(CacheInterface $cache, MarkdownReader $markdownReader, string $path, Environment $twig)
    {
        $this->cache = $cache;
        $this->markdownReader = $markdownReader;
        $this->path = $path . '/Wiki/';
        $this->twig = $twig;
    }

    public function findAll(): array
    {
        return $this->cache->get(
            'wiki_all',
            $this->getAllEntries()
        );
    }

    public function findOneByPath(string $path): string
    {
        return $this->cache->get(
            'wiki_' . \md5($path),
            $this->getEntry($path)
        );
    }

    public function finOneRawByPath(string $path): string
    {
        return$this->getRawEntry($path);
    }

    private function getAllEntries(): \Closure
    {
        return function (ItemInterface $item) {
            $item->expiresAfter(1);
            $content = [];
            foreach ((new Finder())->in($this->path)->files()->name('*.md') as $file) {
                $entry = u($file->getBasename())->beforeLast('.');
                $content[$entry->slice(0,1)->upper()->toString()][$entry->toString()] = u($file->getPathname())->replace($this->path, '')->beforeLast('.')->toString();
            }
            array_walk($content, static function ($item, $key) use (&$content) {
                natsort($item);
                $content[$key] = $item;
            });
            return $content;
        };
    }

    private function getEntry(string $path): \Closure
    {
        return function (ItemInterface $item) use ($path) {
            $item->expiresAfter(1);
            return $this->markdownReader->getContent($this->path . $path . '.md');
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
        file_put_contents(
            $filepath,
            $this->twig->render('memo/simple-template.html.twig', ['memo' => $wiki])
        );
        $this->cache->delete('wiki_' . \md5($wiki->getName()));
        $this->cache->get(
            'wiki_' . \md5($wiki->getName()),
            $this->getEntry($wiki->getName())
        );
    }
}
