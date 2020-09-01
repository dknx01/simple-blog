<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 29.06.20
 */

namespace App\ContentLister;

use App\Entity\DirectoryContent;
use App\Entity\DirectoryContentCollection;
use App\Entity\MemoContent;
use App\MarkdownContent\MarkdownReader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
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
    private MarkdownReader $markdownReader;

    /**
     * ContentLister constructor.
     * @param string $dataPath
     * @param CacheInterface $cache
     * @param MarkdownReader $markdownReader
     */
    public function __construct(string $dataPath, CacheInterface $cache, MarkdownReader $markdownReader)
    {
        $this->dataPath = $dataPath;
        $this->cache = $cache;
        $this->markdownReader = $markdownReader;
    }

    public function listContent(): array
    {
        return $this->cache->get(
            'search',
            $this->readDirectory()
        );
    }

    /**
     * @param string|UnicodeString $path
     * @return MemoContent
     */
    public function getContentForFile(string $path): MemoContent
    {
        $path = u($path);

        if ($path->endsWith('.pdf')) {
            $type = MemoContent::PDF;
        } elseif ($path->endsWith('.docx')) {
            $type = MemoContent::DOCX;
        } else {
            $type = MemoContent::MD;
        }

        $memo = new MemoContent($type, $path);
        if ($memo->isMarkdown()) {
            $memo->setContent($this->markdownReader->getContent($this->dataPath . $path));
        }

        return $memo;
    }

    /**
     * @param $path
     * @return \Closure
     */
    private function readDirectory(): \Closure
    {
        return function(ItemInterface $item) {
            $item->expiresAfter(1);
            $contentCollection = [];

            foreach ((new Finder())->in($this->dataPath)->files()->name(self::AllowedFiles) as $file) {

                $contentCollection[$file->getBasename()] = new DirectoryContent(
                    $file->getFilename(),
                    u($file->getPathname())->replace($this->dataPath, '')->toString(),
                    DirectoryContent::FILE
                );
            }
            return $contentCollection;
        };
    }
}