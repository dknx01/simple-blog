<?php declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\String\AbstractString;

class MemoContent
{
    public const PDF = 'pdf';
    public const MD = 'md';
    public const DOCX = 'docx';

    private string $type;
    private AbstractString $path;
    private ?string $content;

    /**
     * MemoContent constructor.
     * @param string $type
     * @param AbstractString $path
     */
    public function __construct(string $type, AbstractString $path)
    {
        $this->type = $type;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return !$this->isMarkdown()
            ? $this->getPdfPath()
            : $this->path->replace('_', ' ')->replace('-', ' ')->beforeLast('.')->toString();
    }

    public function isMarkdown(): bool
    {
        return $this->type === self::MD;
    }

    public function isDocx(): bool
    {
        return $this->type === self::DOCX;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    private function getPdfPath(): string
    {
        return $this->path->ensureStart('/')->toString();
    }
}