<?php

namespace App\Controller;

use App\ContentLister\ContentLister;
use App\Entity\Memo;
use App\Form\MemoType;
use App\Repository\MemoRepository;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

/**
 * @Route("/admin/memo")
 */
class MemoController extends AbstractController
{
    /**
     * @Route("/new", name="memo_new", methods={"GET","POST"})
     * @IsGranted("ROLE_EDITOR")
     * @param Request $request
     * @param MemoRepository $memoRepository
     * @param ContentLister $contentLister
     * @return Response
     * @throws InvalidArgumentException
     */
    public function new(Request $request, MemoRepository $memoRepository, ContentLister $contentLister): Response
    {
        $memo = new Memo();
        $form = $this->createForm(MemoType::class, $memo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $memoRepository->save($memo);
            $contentLister->refreshContent(
                sprintf(
                    '/Stammtische/%s',
                    $memo->getType(),
                )
            );

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
        return $this->render('memo/show.html.twig', [
            'memo' => $memo,
        ]);
    }

}
