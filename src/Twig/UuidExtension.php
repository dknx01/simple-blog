<?php declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\Uid\Ulid;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UuidExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
          new TwigFunction('uuid', [$this, 'uuid'])
        ];
    }

    public function uuid(): string
    {
        return (string)new Ulid();
    }
}