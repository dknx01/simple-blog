<?php

namespace App\Tests\Entity;

use App\Entity\DirectoryContent;
use PHPUnit\Framework\TestCase;

class DirectoryContentTest extends TestCase
{
    public function testCreateFolderEntry(): void
    {
        $filename = 'foo-bar';
        $path = '/tmp/foo/bla';
        $directoryContent = DirectoryContent::createFolderEntry($filename, $path);

        self::assertEquals($filename, $directoryContent->getFileName());
        self::assertEquals($path, $directoryContent->getPath());

        self::assertEquals('', $directoryContent->getExtension());
        self::assertEquals(DirectoryContent::DIRECTORY, $directoryContent->getType());
        self::assertEquals('', $directoryContent->getReadableFileName());
        self::assertFalse($directoryContent->isDownloadable());
    }

    public function testCreateFileEntry(): void
    {
        $filename = 'foo-bar.pdf';
        $path = '/tmp/foo/bla';
        $directoryContent = DirectoryContent::createFileEntry($filename, $path);

        self::assertEquals($filename, $directoryContent->getFileName());
        self::assertEquals($path, $directoryContent->getPath());

        self::assertEquals('pdf', $directoryContent->getExtension());
        self::assertEquals(DirectoryContent::FILE, $directoryContent->getType());
        self::assertEquals('foo bar', $directoryContent->getReadableFileName());
        self::assertFalse($directoryContent->isDownloadable());
    }

    public function testCreateFileEntryDownloadable(): void
    {
        $filename = 'foo-bar.svg';
        $path = '/tmp/foo/bla';
        $directoryContent = DirectoryContent::createFileEntry($filename, $path);

        self::assertEquals($filename, $directoryContent->getFileName());
        self::assertEquals($path, $directoryContent->getPath());

        self::assertEquals('svg', $directoryContent->getExtension());
        self::assertEquals(DirectoryContent::FILE, $directoryContent->getType());
        self::assertEquals('foo bar', $directoryContent->getReadableFileName());
        self::assertTrue($directoryContent->isDownloadable());
    }
}
