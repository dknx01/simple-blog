<?php

namespace App\Repository;

use App\Entity\Memo;
use App\Entity\MemoEdit;
use Twig\Environment;
use function Symfony\Component\String\u;

class MemoRepository
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

    public function save(Memo $memo): void
    {
        $filepath = sprintf(
            '%s/Stammtische/%s/%s_%s.md',
            $this->path,
            $memo->getType(),
            u($memo->getTitle())->replace(' ', '_')->toString(),
            $memo->getDate()->format('d.m.Y')
        );
        file_put_contents(
            $filepath,
            $this->twig->render('memo/memo-template.html.twig', ['memo' => $memo])
        );
    }

    public function updateMemo(MemoEdit $memoEdit): void
    {
        file_put_contents(
            $this->path . $memoEdit->getPath(),
            $this->twig->render('memo/link-collection-template.html.twig', ['memo' => $memoEdit])
        );
    }
}
