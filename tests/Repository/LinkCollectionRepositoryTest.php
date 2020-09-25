<?php

namespace App\Tests\Repository;

use App\Entity\LinkCollection;
use App\Repository\LinkCollectionRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Twig\Environment;

class LinkCollectionRepositoryTest extends TestCase
{
    use ProphecyTrait;

    private string $path = __DIR__ . '/Anderes/Linksammlung.md';

    protected function setUp():void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
        if (!is_dir(__DIR__ . '/Anderes/')) {
            mkdir(__DIR__ . '/Anderes/');
        }
    }

    public function tearDown(): void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
            rmdir(__DIR__ . '/Anderes/');
        }
    }

    public function testSave(): void
    {
        $linkCollection = new LinkCollection();
        $linkCollection->setContent('bla vla vla');

        $twig = $this->prophesize(Environment::class);
        $twig->render('memo/link-collection-template.html.twig', ['memo' => $linkCollection])
            ->shouldBeCalled()
            ->willReturn($linkCollection->getContent());

        $repo = new LinkCollectionRepository($twig->reveal(), __DIR__);

        $repo->save($linkCollection);
        self::assertFileExists($this->path);
        self::assertEquals('bla vla vla', \file_get_contents($this->path));
    }
}
