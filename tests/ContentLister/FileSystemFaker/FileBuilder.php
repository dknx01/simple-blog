<?php declare(strict_types=1);

namespace App\Tests\ContentLister\FileSystemFaker;

use Faker\Factory;
use Faker\Generator;
use phpDocumentor\Reflection\Types\Callable_;

class FileBuilder
{
    private string $path = '/';
    private string $fileName = 'example.txt';
    private $content;
    private string $rawString;

    public function __construct()
    {
        $this->rawString = Factory::create()->text();
        $this->content = [$this, 'rawContent'];
    }

    public function withPath(string $path): self
    {
        $this->path = ($path[0] === '/') ? $path : '/' . $path;
        if ($this->path[strlen($this->path) - 1] !== '/') {
            $this->path .= '/';
        }
        return $this;
    }

    public function withFileName(string $fileName) :self
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @param callable|string $content
     * @return $this
     */
    public function withContent($content): self
    {
        if (is_string($content)) {
            $this->rawString = $content;
            $this->content = [$this, 'rawContent'];
            return $this;
        }
        if (is_callable($content)) {
            $this->content = $content;
            return $this;
        }
        throw new \Exception('content must be a callable or a string');
    }

    public function rawContent(): string
    {
        return $this->rawString;
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
    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getCompleteFilePath(): string
    {
        return $this->path . $this->fileName;
    }

    /**
     * @return callable
     */
    public function getContent(): callable
    {
        return $this->content;
    }
}