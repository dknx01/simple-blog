<?php

namespace App\Controller;

use App\Entity\Memo;
use App\Form\MemoType;
use App\Repository\MemoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/memo")
 */
class MemoController extends AbstractController
{
    /**
     * @Route("/new", name="memo_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param MemoRepository $memoRepository
     * @return Response
     */
    public function new(Request $request, MemoRepository $memoRepository): Response
    {
        $memo = new Memo();
        $form = $this->createForm(MemoType::class, $memo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $memoRepository->save($memo);

        }

        return $this->render('memo/new.html.twig', [
            'memo' => $memo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="memo_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
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
