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
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use function Symfony\Component\String\u;

class ContentLister
{
    private const AllowedFiles = [
        '*.md',
        '*.pdf',
        '*.docx',
        '*.TTF',
        '*.svg',
        '*.xcf',
        '*.ai',
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

    /**
     * @param string $path
     * @return DirectoryContentCollection
     * @throws InvalidArgumentException
     */
    public function listContent(string $path): DirectoryContentCollection
    {
        return $this->cache->get(
            md5($path),
            $this->readDirectory($path)
        );
    }

    public function refreshContent(string $path): void
    {
        $pathMd5 = \md5($path);
        $this->cache->delete($pathMd5);
        $this->cache->get(
            $pathMd5,
            $this->readDirectory($path)
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
    private function readDirectory($path): \Closure
    {
        return function(ItemInterface $item) use ($path) {
            $item->expiresAfter(3600);
            $contentCollection = new DirectoryContentCollection();
            $reader = new Finder();
            foreach ((new Finder())->in($this->dataPath . $path)->directories()->sortByName() as $folder) {
                if ((new Finder())->in($folder->getPathname())->files()->name(self::AllowedFiles)->hasResults())
                {
                    $contentCollection->addFolder(DirectoryContent::createFolderEntry(
                        $folder->getRelativePathname(),
                        u($folder->getPathname())->replace($this->dataPath . $path, '')->toString()
                    ));
                }
            }

            $content = $reader->in($this->dataPath . $path)->depth(0)->files()->sortByName()->reverseSorting()->name(self::AllowedFiles);
            foreach ($content as $entry) {
                $fileName = u($entry->getBasename());
                $contentCollection->addFile(
                    DirectoryContent::createFileEntry(
                        $fileName,
                        u($entry->getPath())->replace($this->dataPath, '')->after('/')->replace('_', ' ')->replace('-', ' ')->toString()
                    )
                );
            }
            return $contentCollection;
        };
    }
}