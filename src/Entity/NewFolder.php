<?php declare(strict_types=1);

namespace App\Entity;

class NewFolder
{
    private string $parent;
    private string $foldername = '';

    /**
     * @return string
     */
    public function getParent(): string
    {
        return $this->parent;
    }

    /**
     * @param string $parent
     */
    public function setParent(string $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getFoldername(): string
    {
        return $this->foldername;
    }

    /**
     * @param string $foldername
     */
    public function setFoldername(string $foldername): void
    {
        $this->foldername = $foldername;
    }
}