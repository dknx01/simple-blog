<?php

namespace App\Tests\Entity;

use App\Entity\MemoContent;
use PHPUnit\Framework\TestCase;
use function Symfony\Component\String\u;

class MemoContentTest extends TestCase
{
    public function testMemoContentWithMarkdown(): void
    {
        $type = MemoContent::MD;
        $path = u('foo/bar/file.md');
        $memoContent = new MemoContent($type, $path);

        self::assertEquals(MemoContent::MD, $memoContent->getType());
        self::assertEquals('foo/bar/file', $memoContent->getPath());
        self::assertNull($memoContent->getContent());
        self::assertTrue($memoContent->isMarkdown());
    }

    public function testMemoContentWithDocx(): void
    {
        $type = MemoContent::DOCX;
        $path = u('foo/bar/file.docx');
        $memoContent = new MemoContent($type, $path);
        $memoContent->setContent('Content');

        self::assertEquals(MemoContent::DOCX, $memoContent->getType());
        self::assertEquals('/foo/bar/file.docx', $memoContent->getPath());
        self::assertEquals('Content', $memoContent->getContent());
        self::assertTrue($memoContent->isDocx());
    }

    public function testMemoContentWithPdf(): void
    {
        $type = MemoContent::PDF;
        $path = u('foo/bar/file.pdf');
        $memoContent = new MemoContent($type, $path);
        $memoContent->setContent('Content');

        self::assertEquals(MemoContent::PDF, $memoContent->getType());
        self::assertEquals('/foo/bar/file.pdf', $memoContent->getPath());
        self::assertEquals('Content', $memoContent->getContent());
        self::assertFalse($memoContent->isMarkdown());
        self::assertFalse($memoContent->isDocx());
    }
}
