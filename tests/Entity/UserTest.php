<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUser(): void
    {
        $user = new User();
        $user->setPassword('2132ecqwe');
        $user->setUsername('fooo');

        self::assertEquals(['ROLE_USER'], $user->getRoles());
        self::assertEquals('2132ecqwe', $user->getPassword());
        self::assertEquals('fooo', $user->getUsername());
        self::assertNull($user->getId());
        self::assertNull($user->getSalt());
        self::assertNull($user->eraseCredentials());

        $user->setRoles(['ROLE_USER', 'ROLE_FOOO']);
        self::assertEquals(['ROLE_USER', 'ROLE_FOOO'], $user->getRoles());
    }
}
