<?php

namespace App\ContentLister\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class FolderCollection extends ArrayCollection
{
    public function addEntry(FolderEntry $element): bool
    {
        return parent::add($element);
    }

    public function existsEntry(string $path): bool
    {
        /**
         * @param $key
         * @param FolderEntry $element
         * @return bool
         */
        $p = fn ($key, FolderEntry $element) => $element->getPath() === $path;
        return $this->exists($p);
    }
}