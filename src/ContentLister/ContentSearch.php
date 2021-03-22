<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 29.06.20
 */

namespace App\ContentLister;

use App\Entity\SearchResult;
use App\Repository\MemoRepository;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Finder\Finder;
use Symfony\Contracts\Cache\CacheInterface;
use function Symfony\Component\String\u;

class ContentSearch
{
    private MemoRepository $memoRepository;
    private CacheInterface $cache;

    /**
     * ContentLister constructor.
     * @param MemoRepository $memoRepository
     * @param CacheInterface $cache
     */
    public function __construct(MemoRepository $memoRepository, CacheInterface $cache)
    {
        $this->memoRepository = $memoRepository;
        $this->cache = $cache;
    }

    public function findContent(string $content): array
    {
        return $this->cache->get('search_' . \md5($content), $this->findEntries($content));
    }

    private function findEntries(string $content): \Closure
    {
        return function (CacheItem $item) use ($content) {
            $item->expiresAfter(10);
            $contentCollection = [];
            $contentCollection = $this->addResults($this->memoRepository->findFolderName($content), $contentCollection, 'folder');
            $contentCollection = $this->addResults($this->memoRepository->findFileName($content), $contentCollection, 'files');
            $contentCollection = $this->addResults($this->memoRepository->findContent($content), $contentCollection, 'content');

            return $contentCollection;
        };
    }

    /**
     * @param array $result
     * @param array $contentCollection
     * @param string $section
     * @return array
     */
    private function addResults(array $result, array $contentCollection, string $section): array
    {
        if (!empty($result)) {
            foreach ($result as $entry) {
                if (
                    array_key_exists($section, $contentCollection)
                    && array_key_exists($entry['name'], $contentCollection[$section])
                ) {
                    continue;
                }
                $contentCollection[$section][$entry['name']] = $entry;
            }
        }
        return $contentCollection;
    }
}