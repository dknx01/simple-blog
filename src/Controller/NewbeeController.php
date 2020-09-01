<?php

namespace App\Controller;

use App\Entity\LinkCollection;
use App\Form\LinkType;
use App\MarkdownContent\MarkdownReader;
use App\Repository\LinkCollectionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class NewbeeController extends AbstractController
{
    private string $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @Route("/neu_dabei", name="neu_dabei-edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param LinkCollectionRepository $repository
     * @param MarkdownReader $markdownReader
     * @return Response
     */
    public function edit(
        Request $request,
        LinkCollectionRepository $repository,
        MarkdownReader $markdownReader
    ): Response
    {
        $linkCollection = new LinkCollection();
        $linkCollection->setContent(file_get_contents($this->path));

        $form = $this->createForm(LinkType::class, $linkCollection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->save($linkCollection);
            $markdownReader->refreshContent($this->path);

        }

        return $this->render('memo/linkk-collection.html.twig', [
            'linkCollection' => $linkCollection,
            'form' => $form->createView(),
        ]);
    }
}
