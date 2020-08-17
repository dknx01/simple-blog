<?php declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

class DirectoryContent
{
    public const DIRECTORY = 0;
    public const FILE = 1;
    private const DOWNLOADABLE = [
        'docx',
        'TTF',
        'svg',
        'xcf',
    ];

    private UnicodeString $fileName;
    private string $path;
    private int $type;
    private string $extension;

    /**
     * DirectoryContent constructor.
     * @param string $fileName
     * @param string $path
     * @param int $type
     */
    public function __construct(string $fileName, string $path, int $type)
    {
        $this->fileName = u($fileName);
        $this->path = $path;
        $this->type = $type;
        $this->extension = $this->fileName->afterLast('.')->toString();
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName->toString();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function getReadableFileName(): string
    {
        return $this->fileName->beforeLast('.')->replace('_', ' ')->replace('-', ' ')->toString();
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    public function isDownloadable(): bool
    {
        return in_array($this->extension, self::DOWNLOADABLE);
    }

    public static function createFolderEntry(string $fileName, string $path): self
    {
        return new self($fileName, $path, self::DIRECTORY);
    }

    public static function createFileEntry(string $fileName, string $path): self
    {
        return new self($fileName, $path, self::FILE);
    }
}