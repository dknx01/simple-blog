<?php

namespace App\ContentLister\Entity;

use Symfony\Component\String\UnicodeString;

class FolderEntry
{
    public const DIRECTORY = 0;
    public const FILE = 1;
    private UnicodeString $fileName;
    private string $path;
    private int $type;
    private string $extension;
    private bool $onDisk = false;

    /**
     * @return UnicodeString
     */
    public function getFileName(): UnicodeString
    {
        return $this->fileName;
    }

    /**
     * @param UnicodeString $fileName
     */
    public function setFileName(UnicodeString $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return bool
     */
    public function isOnDisk(): bool
    {
        return $this->onDisk;
    }

    /**
     * @param bool $onDisk
     */
    public function setOnDisk(bool $onDisk): void
    {
        $this->onDisk = $onDisk;
    }
}