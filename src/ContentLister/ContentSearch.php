<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 29.06.20
 */

namespace App\ContentLister;

use App\Entity\SearchResult;
use Symfony\Component\Finder\Finder;
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

    /**
     * ContentLister constructor.
     * @param string $dataPath
     */
    public function __construct(string $dataPath)
    {
        $this->dataPath = $dataPath;
    }

    public function listContent(string $content): array
    {
        return $this->readDirectory($content);
    }

    public function findContent(string $content): array
    {
        return $this->readFiles($content);
    }

    /**
     * @param string $content
     * @return array
     */
    private function readDirectory(string $content): array
    {
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
    }

    private function readFiles(string $content): array
    {
        $contentCollection = [];

        foreach ((new Finder())->in($this->dataPath)->files()->name(['*.md', '*.ptxt'])->contains($content) as $file) {
            $contentCollection[] = new SearchResult(
                $file->getBasename(),
                $file->getFilename(),
                u($file->getPathname())->replace($this->dataPath, '')->toString()
            );
        }

       return $contentCollection;
    }
}