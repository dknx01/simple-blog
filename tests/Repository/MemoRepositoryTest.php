<?php

namespace App\Tests\Repository;

use App\Entity\Memo;
use App\Entity\MemoEdit;
use App\Repository\MemoRepository;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twig\Environment;

class MemoRepositoryTest extends TestCase
{
    use ProphecyTrait;

    private static string $path = __DIR__ . '/Stammtische';

    public static function setUpBeforeClass(): void
    {
        self::clearDirectory();
        mkdir(self::$path);
        mkdir(self::$path. '/LV');
    }

    public static function tearDownAfterClass(): void
    {
        self::clearDirectory();
    }

    public function testSave(): void
    {
        $date = new \DateTimeImmutable();
        $memo = new Memo();
        $memo->setContent('bla vla vla');
        $memo->setType('LV');
        $memo->setDate($date);
        $memo->setTitle('A nice title');

        $twig = $this->prophesize(Environment::class);
        $twig->render('memo/memo-template.html.twig', ['memo' => $memo])
            ->shouldBeCalled()
            ->willReturn($memo->getContent());

        $repo = new MemoRepository($twig->reveal(), __DIR__);

        $repo->save($memo);

        $expectedPath = self::$path . '/LV/A_nice_title_' . $date->format('d.m.Y') . '.md';

        self::assertTrue(\file_exists($expectedPath));
        self::assertEquals('bla vla vla', \file_get_contents($expectedPath));
    }

    public function testUpdateMemo(): void
    {
        $date = new \DateTimeImmutable();
        $memo = new MemoEdit();
        $memo->setContent('bla vla vla');
        $memo->setPath('/Stammtische/foo.md');

        $twig = $this->prophesize(Environment::class);
        $twig->render('memo/link-collection-template.html.twig', ['memo' => $memo])
            ->shouldBeCalled()
            ->willReturn($memo->getContent());

        $repo = new MemoRepository($twig->reveal(), __DIR__);

        $repo->updateMemo($memo);

        self::assertTrue(\file_exists(self::$path . '/foo.md'));
        self::assertEquals('bla vla vla', \file_get_contents(self::$path . '/foo.md'));
    }

    protected static function clearDirectory(): void
    {
        if (is_dir(self::$path)) {
            $di = new RecursiveDirectoryIterator(self::$path, FilesystemIterator::SKIP_DOTS);
            $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($ri as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }
            rmdir(self::$path);
        }
    }
}
