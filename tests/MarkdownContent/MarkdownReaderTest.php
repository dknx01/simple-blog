<?php

namespace App\Tests\MarkdownContent;

use App\MarkdownContent\MarkdownReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\ItemInterface;

class MarkdownReaderTest extends TestCase
{
    protected function tearDown(): void
    {
        $filename = __DIR__ . '/example.md';
        if (\file_exists($filename)) {
            unlink($filename);
        }
    }

    public function testGetContentFromCache(): void
    {
        $adapter = new ArrayAdapter();
        $adapter->clear();
        $adapter->get(md5('foo/bar.md'), static function(ItemInterface $item) {
            return '**Just** a Text';
        });
        $reader = new MarkdownReader($adapter);

        self::assertEquals('**Just** a Text', $reader->getContent('foo/bar.md'));
        $adapter->clear();
    }

    public function testRefreshContent(): void
    {
        $filename = $this->writeTestFile();
        $adapter = new ArrayAdapter();
        $adapter->clear();
        $adapter->get(md5($filename), static function(ItemInterface $item) {
            return 'nothing';
        });

        $reader = new MarkdownReader($adapter);
        $reader->refreshContent($filename);

        $expectedText = <<<'HTML'
<h1>Hello</h1>

<p><div class="mermaid">
graph TD;
    A-->B;
    A-->C;
    B-->D;
    C-->D;
</div></p>

HTML;
        self::assertEquals($expectedText, $reader->getContent($filename));
        $adapter->clear();
    }

    public function testGetContentFromFile(): void
    {
        $filename = $this->writeTestFile();
        $adapter = new ArrayAdapter();
        $adapter->clear();

        $reader = new MarkdownReader($adapter);

        $expectedText = <<<'HTML'
<h1>Hello</h1>

<p><div class="mermaid">
graph TD;
    A-->B;
    A-->C;
    B-->D;
    C-->D;
</div></p>

HTML;
        self::assertEquals($expectedText, $reader->getContent($filename));
        $adapter->clear();
    }

    /**
     * @return string
     */
    private function writeTestFile(): string
    {
        $filename = __DIR__ . '/example.md';
        if (\file_exists($filename)) {
            unlink($filename);
        }
        \file_put_contents($filename, <<<'MD'
# Hello
~~~mermaid
graph TD;
    A-->B;
    A-->C;
    B-->D;
    C-->D;
~~~
MD
        );
        return $filename;
    }
}
