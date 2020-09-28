<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 29.06.20
 */

namespace App\ContentLister;

use App\Entity\SearchResult;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Cache\CacheInterface;
use function Symfony\Component\String\u;

class ContentSearch
{
    private const AllowedFiles = [
        '*.md',
        '*.pdf',
        '*.docx',
        '*.TTF',
        '*.svg',
        '*.xcf',
    ];
    private string $dataPath;
    private CacheInterface $cache;

    /**
     * ContentLister constructor.
     * @param string $dataPath
     * @param CacheInterface $cache
     */
    public function __construct(string $dataPath, CacheInterface $cache)
    {
        $this->dataPath = $dataPath;
        $this->cache = $cache;
    }

    public function listContent(string $content): array
    {
        return $this->cache->get('searchDir_' . \md5($content), $this->readDirectory($content));
    }

    public function findContent(string $content): array
    {
        return $this->cache->get('searchFiles_' . \md5($content), $this->readFiles($content));
    }

    /**
     * @param string $content
     * @return \Closure
     */
    private function readDirectory(string $content): \Closure
    {
        return function (CacheItem $item) use ($content) {
            $contentCollection = [];

            $fileNames = [];
            foreach (self::AllowedFiles as $allowedFile) {
                $fileNames[] = '~.*' . $content . '.' . $allowedFile . '~i';
            }
            foreach ((new Finder())->in($this->dataPath)->files()->name($fileNames) as $file) {
                $contentCollection[] = new SearchResult(
                    $file->getBasename(),
                    $file->getFilename(),
                    u($file->getPathname())->replace($this->dataPath, '')->toString()
                );
            }
            return $contentCollection;
        };

    }

    private function readFiles(string $content): \Closure
    {
        return function (CacheItem $item) use ($content) {
            $contentCollection = [];

            foreach ((new Finder())->in($this->dataPath)->files()->name(['*.md', '*.ptxt'])->contains('~'.$content.'~i') as $file) {
                $baseName = u($file->getBasename());
                $fileName = u($file->getFilename());
                $path = u($file->getPathname())->replace($this->dataPath, '');
                if ($baseName->endsWith('ptxt')) {
                    $baseName = $baseName->replace('.ptxt', '.pdf');
                    $fileName = $fileName->replace('.ptxt', '.pdf');
                    $path = $path->replace('.ptxt', '.pdf');
                }
                $contentCollection[] = new SearchResult(
                    $baseName->toString(),
                    $fileName->toString(),
                    $path->toString()
                );
            }

            return $contentCollection;
        };
    }
}