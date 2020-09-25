<?php

namespace App\Repository;

use App\Entity\Memo;
use App\Entity\MemoEdit;
use App\Entity\NewDocument;
use Twig\Environment;
use function Symfony\Component\String\u;

class NewDocumentRepository
{
    private Environment $twig;
    private string $path;

    /**
     * MemoRepository constructor.
     * @param Environment $twig
     * @param string $path
     */
    public function __construct(Environment $twig, string $path)
    {
        $this->twig = $twig;
        $this->path = $path;
    }

    public function save(NewDocument $newDocument): void
    {
        $filepath = sprintf(
            '%s/%s.md',
            $this->path,
            $newDocument->getPath()
        );
        file_put_contents(
            $filepath,
            $this->twig->render('memo/simple-template.html.twig', ['memo' => $newDocument])
        );
    }
}
