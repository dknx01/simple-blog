<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 11.07.20
 */

namespace App\Entity;

class DirectoryContentCollection
{
    private array $directories = [];
    private array $files = [];

    public function addFolder(DirectoryContent $directoryContent): void
    {
        $this->directories[] = $directoryContent;
    }
    public function addFile(DirectoryContent $directoryContent): void
    {
        $this->files[] = $directoryContent;
    }

    /**
     * @return array
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

}