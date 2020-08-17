<?php

namespace App\Repository;

use App\Entity\LinkCollection;
use Twig\Environment;

class LinkCollectionRepository
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

    public function save(LinkCollection $linkcollection): void
    {
        $filepath = sprintf(
            '%s/Anderes/Linksammlung.md',
            $this->path
        );
        file_put_contents(
            $filepath,
            $this->twig->render('memo/link-collection-template.html.twig', ['memo' => $linkcollection])
        );
    }
}
