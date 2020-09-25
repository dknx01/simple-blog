<?php

namespace App\Tests\ContentLister;

use App\ContentLister\ContentLister;
use App\Entity\DirectoryContent;
use App\Entity\MemoContent;
use App\MarkdownContent\MarkdownReader;
use App\Tests\ContentLister\FileSystemFaker\FileBuilder;
use App\Tests\ContentLister\FileSystemFaker\FileSystemFaker;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ContentListerTest extends TestCase
{
    use ProphecyTrait;

    private static FileSystemFaker $fsFaker;

    public static function setUpBeforeClass(): void
    {
        self::$fsFaker = new FileSystemFaker(__DIR__ . '/Foo');
        self::$fsFaker->remove();
        self::$fsFaker->makeDirectories(['A', 'Z']);
    }

    public static function tearDownAfterClass(): void
    {
        self::$fsFaker->remove();
    }

    public function testListContent(): void
    {
        self::$fsFaker->makeFilesWithRandomContent(
            [
                (new FileBuilder())->withPath('/A')->withFileName('example.md'),
                (new FileBuilder())->withPath('/Z')->withFileName('example.md'),
            ]
        );

        $cache = new ArrayAdapter();
        $cache->clear();
        $markdownReader = $this->prophesize(MarkdownReader::class);

        $contentLister = new ContentLister(__DIR__, $cache, $markdownReader->reveal());

        $result = $contentLister->listContent('/Foo');
        self::assertEmpty($result->getFiles());
        $expectedDirectoryA = new DirectoryContent('A', '/A', DirectoryContent::DIRECTORY);
        $expectedDirectoryZ = new DirectoryContent('Z', '/Z', DirectoryContent::DIRECTORY);
        self::assertEquals([$expectedDirectoryA, $expectedDirectoryZ], $result->getDirectories());
        $cache->clear();
    }

    public function testListContentWithFileOnHighestLevel(): void
    {
        self::$fsFaker->remove();
        self::$fsFaker->makeFilesWithRandomContent(
            [
                (new FileBuilder())->withFileName('example.md')
            ]
        );

        $cache = new ArrayAdapter();
        $cache->clear();
        $markdownReader = $this->prophesize(MarkdownReader::class);

        $contentLister = new ContentLister(__DIR__, $cache, $markdownReader->reveal());

        $result = $contentLister->listContent('/Foo');
        self::assertEmpty($result->getDirectories());
        $expectedDirectoryA = new DirectoryContent('example.md', '/Foo', DirectoryContent::FILE);
        self::assertEquals([$expectedDirectoryA], $result->getFiles());
        $cache->clear();
    }

    public function testGetContentForFilePdf(): void
    {
        $cache = new ArrayAdapter();
        $cache->clear();
        $markdownReader = $this->prophesize(MarkdownReader::class);

        $contentLister = new ContentLister(__DIR__, $cache, $markdownReader->reveal());
        $result = $contentLister->getContentForFile('/Foo/example.pdf');

        self::assertEquals(MemoContent::PDF, $result->getType());
        self::assertNull($result->getContent());
        self::assertEquals('/Foo/example.pdf', $result->getPath());
    }

    public function testGetContentForFileDocx(): void
    {
        $cache = new ArrayAdapter();
        $cache->clear();
        $markdownReader = $this->prophesize(MarkdownReader::class);

        $contentLister = new ContentLister(__DIR__, $cache, $markdownReader->reveal());
        $result = $contentLister->getContentForFile('/Foo/example.docx');

        self::assertEquals(MemoContent::DOCX, $result->getType());
        self::assertNull($result->getContent());
        self::assertEquals('/Foo/example.docx', $result->getPath());
    }

    public function testGetContentForFileMarkdown(): void
    {
        self::$fsFaker->makeFilesWithRandomContent(
        [
            (new FileBuilder())->withPath('/A')->withFileName('example.md'),
        ]
    );
        $cache = new ArrayAdapter();
        $cache->clear();
        $markdownReader = $this->prophesize(MarkdownReader::class);
        $markdownReader->getContent(Argument::type('string'))
            ->willReturn(\file_get_contents(__DIR__ . '/Foo/A/example.md'));

        $contentLister = new ContentLister(__DIR__, $cache, $markdownReader->reveal());

        $result = $contentLister->getContentForFile('/Foo/A/example.md');
        $markdownReader->getContent(Argument::type('string'))
            ->shouldHaveBeenCalled();

        self::assertEquals(MemoContent::MD, $result->getType());
        self::assertNotNull($result->getContent());
        self::assertEquals('/Foo/A/example', $result->getPath());
    }
}
