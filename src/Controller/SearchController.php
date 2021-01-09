<?php

namespace App\Controller;

use App\ContentLister\ContentSearch;
use App\Security\File\Sanitizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/", name="search")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param ContentSearch $contentSearch
     * @param Request $request
     * @return Response
     */
    public function search(ContentSearch  $contentSearch, Request $request): Response
    {
        $result = [];
        $searchText = '';
        if ($request->getMethod() === 'POST'
            && $this->isCsrfTokenValid('search', $request->request->get('_csrf_token'))
        ) {
            $searchText = $request->request->get('searchText');
            $folderFiles = $contentSearch->listContent($searchText);

            $content = $contentSearch->findContent($searchText);
            $result = array_merge($folderFiles, $content);
        }

        return $this->render('search/index.html.twig', ['name' => $searchText, 'result' => $result, 'searchText' => $searchText]);
    }

    /**
     * @Route("/search_result/{path}", name="search_result")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $path
     * @return Response
     */
    public function searchResult(string $path): Response
    {
        $path = Sanitizer::securePath(urldecode($path));
        $path = u($path);
        if ($path->afterLast('.')->toString() !== 'md') {
            return $this->redirectToRoute('file', ['path' => urlencode($path->toString())]);
        }
        if ($path->startsWith('/Wiki')) {
            return $this->redirectToRoute('wiki_show', ['path' => urlencode($path->after('/Wiki/')->beforeLast('.'))]);
        }
        if ($path->endsWith('Linksammlung.md')) {
            return $this->redirectToRoute('link-collection');
        }
        if ($path->endsWith('neu_dabei.md')) {
            return $this->redirectToRoute('newbees');
        }
        return $this->redirectToRoute('memo', ['path' => urlencode($path)]);
    }
}
