<?php

namespace App\Controller;

use App\Entity\Suggestion;
use App\Form\SuggestionType;
use App\MarkdownContent\MarkdownReader;
use App\Repository\SuggestionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Registry;

/**
 * @Route("/suggestion")
 */
class SuggestionController extends AbstractController
{
    private Registry $workflowRegistry;
    private ChatterInterface $chatter;

    public function __construct(Registry $workflowRegistry, ChatterInterface $chatter)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->chatter = $chatter;
    }

    /**
     * @Route("/", name="suggestion_index", methods={"GET"}))
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param SuggestionRepository $suggestionRepository
     * @return Response
     */
    public function index(SuggestionRepository $suggestionRepository): Response
    {
        return $this->render('suggestion/index.html.twig', [
            'suggestions' => $suggestionRepository->findAll(),
            'excludeClosed' => false
        ]);
    }

    /**
     * @Route("/sort/{excludeClosed}", name="suggestion_index_sorted", methods={"GET"}))
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param SuggestionRepository $suggestionRepository
     * @param string $excludeClosed
     * @return Response
     */
    public function indexSorted(SuggestionRepository $suggestionRepository, string $excludeClosed = ''): Response
    {
        if ($excludeClosed === '') {
            return $this->redirectToRoute('suggestion_index');
        }
        return $this->render('suggestion/index.html.twig', [
            'suggestions' => $suggestionRepository->findAllNotClosed(),
            'excludeClosed' => true
        ]);
    }

    /**
     * @Route("/new", name="suggestion_new", methods={"GET","POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param Request $request
     * @param MarkdownReader $markdownReader
     * @return Response
     */
    public function new(Request $request, MarkdownReader $markdownReader): Response
    {
        $suggestion = new Suggestion();
        $form = $this->createForm(SuggestionType::class, $suggestion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $workflow = $this->workflowRegistry->get($suggestion);
                $workflow->apply($suggestion, 'created');
                $suggestion->setComments('');
                $suggestion->setContent(
                    $markdownReader->parseString($suggestion->getContent())
                );
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($suggestion);
                $entityManager->flush();

                $this->sendChatterMessageForSuggestion($suggestion);
            } catch (LogicException $exception) {
                $this->addFlash('error', $exception->getMessage());
                return $this->render('suggestion/new.html.twig', [
                    'suggestion' => $suggestion,
                    'form' => $form->createView(),
                ]);
            }

            return $this->redirectToRoute('suggestion_index');
        }

        return $this->render('suggestion/new.html.twig', [
            'suggestion' => $suggestion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="suggestion_show", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param Suggestion $suggestion
     * @return Response
     */
    public function show(Suggestion $suggestion): Response
    {
        return $this->render('suggestion/show.html.twig', [
            'suggestion' => $suggestion,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="suggestion_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Suggestion $suggestion
     * @return Response
     */
    public function edit(Request $request, Suggestion $suggestion): Response
    {
        $form = $this->createForm(SuggestionType::class, $suggestion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('suggestion_index');
        }

        return $this->render('suggestion/edit.html.twig', [
            'suggestion' => $suggestion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/action/{id}/{transition}", name="suggestion_transition")
     * @IsGranted("ROLE_ADMIN")
     * @param Suggestion $suggestion
     * @param SuggestionRepository $suggestionRepository
     * @param string $transition
     * @return Response
     */
    public function transition(Suggestion $suggestion, SuggestionRepository $suggestionRepository, string $transition): Response
    {
        try {
            $workflow = $this->workflowRegistry->get($suggestion);
            $workflow->apply($suggestion, urldecode($transition));
            $suggestionRepository->save($suggestion);
        } catch (LogicException $exception) {
            preg_match('/^Transition "(.+).*" /', $exception->getMessage(), $matches);
            $this->addFlash(
                'error',
                $matches[1]
            );
        }

        return $this->redirectToRoute('suggestion_index');
    }

    /**
     * @Route("/comment/{id}/edit", name="suggestion_comment", methods={"GET","POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param Request $request
     * @param SuggestionRepository $suggestionRepository
     * @param Suggestion $suggestion
     * @param MarkdownReader $markdownReader
     * @return Response
     */
    public function comment(Request $request, SuggestionRepository $suggestionRepository, Suggestion $suggestion,  MarkdownReader $markdownReader): Response
    {
        if ($request->getMethod() === 'POST'
            && $this->isCsrfTokenValid('new_suggestion_comment', $request->request->get('_csrf_token'))
        ) {
                $suggestion->setComments($this->handleComments($request, $markdownReader, $suggestion));
                    $suggestionRepository->save($suggestion);
                    return $this->redirectToRoute('suggestion_index');
        }
        return $this->render('suggestion/comment.html.twig', ['name' => 'add_comment', 'suggestion' => $suggestion]);
    }

    /**
     * @Route("/{id}", name="suggestion_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Suggestion $suggestion
     * @return Response
     */
    public function delete(Request $request, Suggestion $suggestion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$suggestion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($suggestion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('suggestion_index');
    }

    private function handleComments(Request $request, MarkdownReader $markdownReader, Suggestion $suggestion): string
    {
        $comment = $markdownReader->parseString(
            '`Datum:' . (new \DateTime())->format('d.m.Y H:i:s') . '`<br>' .
            $request->request->get('comment')
        );
        return $suggestion->getComments() === '' ? $comment : $suggestion->getComments() . '<hr>' . $comment;
    }

    /**
     * @param Suggestion $suggestion
     * @throws TransportExceptionInterface
     */
    private function sendChatterMessageForSuggestion(Suggestion $suggestion): void
    {
        $message = (new ChatMessage('Neuer Vorschlag/Idee'))
            ->subject($this->renderView('suggestion/telegramm_message.html.twig', ['suggestion' => $suggestion]))
            ->transport('telegram');
        $this->chatter->send($message);
    }
}
