<?php

namespace App\Command;

use App\Entity\Memo;
use App\Repository\MemoRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use function Symfony\Component\String\u;

class ImportFilesCommand extends Command
{
    protected static $defaultName = 'importFiles';
    private MemoRepository $memoRepo;
    private string $basePath;
    private Filesystem $fs;

    public function __construct(string $name = null, string $basePath, MemoRepository $memoRepository, Filesystem $fs)
    {
        parent::__construct($name);
        $this->basePath = $basePath;
        $this->memoRepo = $memoRepository;
        $this->fs = $fs;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('path', InputArgument::REQUIRED, 'path under base path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Importing all files from data folder');

        $finder = new Finder();
        $finder->in($input->getArgument('path'));
        $progressbAr = $io->createProgressBar();

        foreach ($finder->files() as $file) {
            if ($file->getExtension() !== 'ptxt') {
                $memo = new Memo();
                $title = substr($file->getFilename(), 0, -(strlen($file->getExtension()) + 1));
                $title = str_replace(['-', '_'], ' ', $title);
                $memo->setLocation(substr($file->getPath(), strlen($this->basePath)))
                    ->setExtension($file->getExtension())
                    ->setOnDisk($file->getExtension() !== 'md')
                    ->setTitle($title)
                    ->setType('LV')
                    ->setFileName($file->getFilename())
                ;
                if (!$memo->getOnDisk()) {
                    $memo->setContent(file_get_contents($file->getPathname()));
                } else {
                    $path = str_replace('.pdf', '.ptxt', $file->getRealPath());
                    if ($memo->getExtension() === 'pdf' && file_exists($path)) {
                        $memo->setContent(file_get_contents($path));
                    }
                }
                $this->memoRepo->save($memo);
                if ($memo->getOnDisk()) {
                    $this->fs->copy($file->getPathname(), $this->basePath . '/' . $memo->getUuid() . $memo->getExtension());
                }
                $progressbAr->advance();
                $progressbAr->display();
            }
        }
        $progressbAr->finish();
        $progressbAr->display();


        return Command::SUCCESS;
    }
}
