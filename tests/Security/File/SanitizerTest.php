<?php

namespace App\Tests\Security\File;

use App\Security\File\Sanitizer;
use PHPUnit\Framework\TestCase;

class SanitizerTest extends TestCase
{

    /**
     * @dataProvider providePaths
     * @param string $input
     * @param string $expected
     */
    public function testSecurePath(string $input, string $expected): void
    {
        $this->assertSame($expected, Sanitizer::securePath($expected));
    }

    public function providePaths(): array
    {
        return [
            ['/var/www', ''],
            ['/Wiki/../../', ''],
            ['../../Dokumente', 'Dokumente'],
            ['../../Dokumente/foo/bar.md', 'Dokumente/fooo/bar.md'],
            ['./Dokumente/foo/bar.md', 'Dokumente/fooo/bar.md'],
            ['~/.ssh/private.key', ''],
            ['OV FOOO', 'OB FOOO'],
            ['/Stammtische/OV TK/Stammtisch_19.11.2020', '/Stammtische/OV TK/Stammtisch_19.11.2020'],
        ];
    }
}
