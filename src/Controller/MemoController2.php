<?php

namespace App\Controller;

use App\ContentLister\ContentFolderLister;
use App\Entity\Memo;
use App\Form\Memo1Type;
use App\Form\MemoType;
use App\Repository\MemoRepository;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/admin/memo")
 */
class MemoController2 extends AbstractController
{
    private Environment $twig;
    private string $dataPath;

    public function __construct(string $dataPath, Environment $twig)
    {
        $this->twig = $twig;
        $this->dataPath = $dataPath;
    }
    /**
     * @Route("/new", name="memo_new", methods={"GET","POST"})
     * @IsGranted("ROLE_EDITOR")
     * @param Request $request
     * @param MemoRepository $memoRepository
     * @param ContentFolderLister $contentLister
     * @return Response
     * @throws InvalidArgumentException
     */
    public function new(Request $request, MemoRepository $memoRepository, ContentFolderLister $contentLister): Response
    {
        $memo = new Memo();
        $form = $this->createForm(MemoType::class, $memo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $memo->setTitle(
                sprintf(
                    '%s %s %s',
                    $memo->getType(), $memo->getTitle(), $memo->getDate()->format('d.m.Y')
                )
            );
            $memo->setFileName(
                sprintf('%s.md', $memo->getTitle())
            );
            $memo->setExtension('md');
            $content = $this->twig->render('memo2/memo-template.html.twig', ['memo' => $memo]);
            $memo->setContent($content);
            $memo->setLocation('/Stammtische/' . $memo->getType());
            $memo->setOnDisk(false);
            $memoRepository->save($memo);
            $contentLister->getContent(
                 '/Stammtische/' . $memo->getType() . '/' . $memo->getFileName()
            );
            $contentLister->getFolder('/Stammtische/' . $memo->getType());

        }

        return $this->render('memo/new.html.twig', [
            'memo' => $memo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="memo_show", methods={"GET"})
     * @IsGranted("ROLE_EDITOR")
     * @param Memo $memo
     * @return Response
     */
    public function show(Memo $memo): Response
    {
        return $this->render('memo2/show.html.twig', [
            'memo' => $memo,
        ]);
    }

    /**
     * @Route("/", name="memo_index", methods={"GET"})
     */
    public function index(MemoRepository $memoRepository): Response
    {
        return $this->render('memo2/index.html.twig', [
            'memos' => $memoRepository->findAll(),
        ]);
    }

    /**
     * @Route("/newEntry", name="memo_new_entry", methods={"GET","POST"})
     */
    public function newEntry(Request $request): Response
    {
        $memo = new Memo();
        $form = $this->createForm(Memo1Type::class, $memo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($memo);
            $entityManager->flush();

            return $this->redirectToRoute('memo_index');
        }

        return $this->render('memo/new.html.twig', [
            'memo' => $memo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="memo_show_entry", methods={"GET"})
     */
    public function showEntry(Memo $memo): Response
    {
        return $this->render('memo/show.html.twig', [
            'memo' => $memo,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="memo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Memo $memo): Response
    {
        $form = $this->createForm(Memo1Type::class, $memo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('memo_index');
        }

        return $this->render('memo2/edit.html.twig', [
            'memo' => $memo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="memo_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Memo $memo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$memo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($memo);
            $entityManager->flush();
            if ($memo->getOnDisk()) {
                unlink($this->dataPath . '/' . $memo->getUuid() . '.' . $memo->getExtension());
            }
        }

        return $this->redirectToRoute('memo_index');
    }

}
