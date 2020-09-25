<?php

namespace App\Tests\Twig;

use App\Twig\UuidExtension;
use PHPUnit\Framework\TestCase;

class UuidExtensionTest extends TestCase
{
    public function testGetFunctions(): void
    {
        $uuidExtension = new UuidExtension();
        $actualResult = $uuidExtension->getFunctions();

        self::assertCount(1, $actualResult);
        self::assertEquals(
            $actualResult[0]->getName(),
            'uuid'
        );
    }

    public function testUuid(): void
    {
        self::assertNotEmpty((new UuidExtension())->uuid());
    }
}
