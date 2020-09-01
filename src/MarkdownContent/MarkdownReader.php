<?php declare(strict_types=1);

namespace App\MarkdownContent;

use Michelf\Markdown;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use TextParser\Parser;

class MarkdownReader
{
    private const START_MERMAID = '~~~mermaid';
    private const END_MERMAID = '~~~';
    private const DIV_CLASS_MERMAID_START = '<div class="mermaid">';
    private const DIV_CLASS_MERMAID_END = '</div>';

    private CacheInterface $cache;

    /**
     * MarkdownReader constructor.
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getContent(string $path): string
    {
        $key = md5($path);
        return $this->cache->get(
            $key,
            $this->parseFile($path)
        );
    }

    /**
     * @param string $path
     * @throws InvalidArgumentException
     */
    public function refreshContent(string $path): void
    {
        $key = md5($path);
        $this->cache->delete($key);
        $this->cache->get(
            $key,
            $this->parseFile($path)
        );
    }

    private function parseFile(string $path): \Closure
    {
        return function(ItemInterface $item) use ($path) {
            $item->expiresAfter(3600);

            $content = Markdown::defaultTransform(
                file_get_contents($path)
            );
            return $this->parseMermaid($content);
        };
    }

    /**
     * @param string $content
     * @return string
     */
    private function parseMermaid(string $content): string
    {
        $mermaidText = Parser::findOne($content, self::START_MERMAID, self::END_MERMAID);

        $search = self::START_MERMAID . $mermaidText . self::END_MERMAID;
        $replace = self::DIV_CLASS_MERMAID_START . $mermaidText . self::DIV_CLASS_MERMAID_END;
        $content = str_replace($search, $replace, $content);

        $mermaidTextOriginal = Parser::findOne($content, self::DIV_CLASS_MERMAID_START, self::DIV_CLASS_MERMAID_END);
        $mermaidText = str_replace(['<p>', '</p>'], '', $mermaidTextOriginal);

        return str_replace($mermaidTextOriginal, $mermaidText, $content);
    }
}