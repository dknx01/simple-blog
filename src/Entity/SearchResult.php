<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 25.09.20
 */

namespace App\Entity;

class SearchResult
{
    private string $name;
    private string $path;
    private string $filename;

    /**
     * SearchResult constructor.
     * @param string $name
     * @param string $filename
     * @param string $path
     */
    public function __construct(string $name, string $filename,string $path)
    {
        $this->name = $name;
        $this->path = $path;
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}