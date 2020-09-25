<?php declare(strict_types=1);

namespace App\Tests\ContentLister\FileSystemFaker;

use Faker\{Factory,Generator};
use Symfony\Component\Filesystem\Filesystem;
use Traversable;
use function count;
use function is_array;

class FileSystemFaker
{
    private Filesystem $filesystem;
    private string $root;
    private Generator $faker;

    /**
     * @param string|null $root
     */
    public function __construct(string $root = null)
    {
        $this->faker = Factory::create();
        $this->root = $root ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->faker->uuid;
        $this->filesystem = new Filesystem();
    }

    /**
     * @param null|string|Traversable $files
     */
    public function remove($files = null): void
    {
        if ($files === null) {
            $files = [];
        } elseif ($files instanceof Traversable) {
            $files = iterator_to_array($files, false);
        } elseif (!is_array($files)) {
            $files = [$files];
        }
        array_walk($files, static function ($path, $key, $root) use (&$files) {
            $files[$key] = $root !== '' ? ($root . '/' . $path) : $path;
        }, $this->root);
        if (count($files) === 0) {
            $files = [$this->root];
        }
        $this->filesystem->remove($files);
    }

    /**
     * @param FileBuilder[]|FileBuilder $fileBuilders
     */
    public function makeFilesWithRandomContent($fileBuilders): void
    {
        $fileBuilders = is_iterable($fileBuilders) ? $fileBuilders : [$fileBuilders];
        foreach ($fileBuilders as $fileBuilder) {
            $this->filesystem->dumpFile($this->root . $fileBuilder->getCompleteFilePath(), $this->faker->text());
        }
    }

    /**
     * @param FileBuilder[]|FileBuilder $builder
     */
    public function makeFilesWithContent($builder): void
    {
        $fileBuilders = is_iterable($builder) ? $builder : [$builder];
        foreach ($fileBuilders as $fileBuilder) {
            $content = $fileBuilder->getContent()();
            $this->filesystem->dumpFile(
                $this->root . $fileBuilder->getCompleteFilePath(),
                $content
            );
        }
    }

    /**
     * @param string|iterable $dirPath
     */
    public function makeDirectories($dirPath): void
    {
        $dirPath = is_iterable($dirPath) ? $dirPath : [$dirPath];
        foreach ($dirPath as $path) {
            $this->filesystem->mkdir($this->root . '/' . $path);
        }
    }
}