<?php

namespace App\ContentLister;

use App\ContentLister\Entity\FolderCollection;
use App\ContentLister\Entity\FolderEntry;
use App\MarkdownContent\MarkdownReader;
use App\Repository\MemoRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use function Symfony\Component\String\u;

class ContentFolderLister
{
    private MemoRepository $memoRepo;
    private CacheInterface $cache;
    private MarkdownReader $markdownReader;

    public function __construct(CacheInterface $cache, MemoRepository $memoRepository, MarkdownReader $markdownReader)
    {
        $this->cache = $cache;
        $this->memoRepo = $memoRepository;
        $this->markdownReader = $markdownReader;
    }

    public function getContent(string $path): array
    {
        return $this->cache->get(
            md5($path),
            $this->getFileContent($path)
        );
    }

    public function getFolder(string $folder): FolderCollection
    {
        return $this->cache->get(
            md5($folder), $this->findFolderContent($folder)
        );
    }

    private function findFolderContent(string $folder): \Closure
    {
        return function (ItemInterface $item) use ($folder) {
            $item->expiresAfter(10);
            $content = $this->memoRepo->findByLocation($folder);
            $result = new FolderCollection();
            foreach ($content as $entry) {
                if ($entry['location'] === $folder) {
                    $folderEntry = new FolderEntry();
                    $folderEntry->setType(FolderEntry::FILE);
                    $folderEntry->setPath($entry['location']);
                    $folderEntry->setExtension($entry['extension']);
                    $folderEntry->setOnDisk($entry['onDisk']);
                    $folderEntry->setFileName(u($entry['fileName']));
                    $result->addEntry($folderEntry);
                } else {
                    $locationMemo = u($entry['location']);
                    $regexLocation = substr($folder, -1) === '/' ? $folder : ($folder . '/');
                    if ($locationMemo->match('~^' . $regexLocation . '[^/]+$~') && !$result->existsEntry($locationMemo->toString())) {
                        $folderEntry = new FolderEntry();
                        $folderEntry->setType(FolderEntry::DIRECTORY);
                        $folderEntry->setPath($entry['location']);
                        $folderEntry->setFileName(u($entry['location'])->replace($regexLocation, '')->toUnicodeString());
                        $result->addEntry($folderEntry);
                    }
                }
            }
            return $result;
        };
    }

    private function getFileContent(string $path): \Closure
    {
        return function (ItemInterface $item) use ($path) {
            $item->expiresAfter(10);
            $memo = $this->memoRepo->findMemo($path);
            if ($memo === false) {
                throw new \RuntimeException('Path not found: ' . $path);
            }
            if ($memo['extension'] === 'md') {
                $memo['content'] = $this->markdownReader->parseString($memo['content']);
            }
            $memo['path'] = $memo['location'] . '/' . $memo['file_name'];

            return $memo;
        };
    }
}