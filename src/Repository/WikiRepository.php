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
    private Filesystem $filesystem;

    public function __construct(
        CacheInterface $cache,
        MarkdownReader $markdownReader,
        string $path,
        Environment $twig,
        Filesystem $filesystem
    ) {
        $this->cache = $cache;
        $this->markdownReader = $markdownReader;
        $this->path = $path . '/Wiki/';
        $this->twig = $twig;
        $this->filesystem = $filesystem;
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
            $content = [];
            foreach ((new Finder())->in($this->path)->files()->name('*.md')->sort($this->sort()) as $file) {
                $entry = u($file->getBasename())->beforeLast('.');
                $fileName = u($file->getPathname())->replace($this->path, '')->beforeLast('.')->toString();
                $content[$entry->slice(0,1)->upper()->toString()][$entry->toString()] = $fileName;
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
