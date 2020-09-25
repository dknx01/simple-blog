<?php

namespace App\Controller;

use App\Entity\Wiki;
use App\Form\WikiType;
use App\Repository\WikiRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

/**
 * @Route("/wiki")
 */
class WikiController extends AbstractController
{
    /**
     * @Route("/", name="wiki_index", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function index(WikiRepository $wikiRepository): Response
    {
        return $this->render('wiki/index.html.twig', [
            'wikis' => $wikiRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="wiki_new", methods={"GET","POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function new(Request $request): Response
    {
        $wiki = new Wiki();
        $form = $this->createForm(WikiType::class, $wiki);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wiki);
            $entityManager->flush();

            return $this->redirectToRoute('wiki_index');
        }

        return $this->render('wiki/new.html.twig', [
            'wiki' => $wiki,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{path}", name="wiki_show", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $path
     * @param WikiRepository $wikiRepository
     * @return Response
     */
    public function show(string $path, WikiRepository $wikiRepository): Response
    {
        $path = urldecode($path);
        return $this->render('wiki/show.html.twig', [
            'content' => $wikiRepository->findOneByPath($path),
            'title' => u($path)->afterLast('/')->toString(),
            'path' => $path
        ]);
    }

    /**
     * @Route("/edit/{path}", name="wiki_edit", methods={"GET","POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function edit(Request $request, string $path, WikiRepository $wikiRepository): Response
    {
        $path = urldecode($path);
        $wiki = new Wiki();
        if ($request->getMethod() === Request::METHOD_GET) {
            $wiki->setName($path);
            $wiki->setContent($wikiRepository->finOneRawByPath($path));
        }
        $form = $this->createForm(WikiType::class, $wiki);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wikiRepository->save($form->getData());
            //return $this->redirectToRoute('wiki_index');
        }

        return $this->render('wiki/edit.html.twig', [
            'wiki' => $wiki,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wiki_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Wiki $wiki): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wiki->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($wiki);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wiki_index');
    }
}
