<?php

namespace App\Controller;

use App\ContentLister\ContentLister;
use App\ContentLister\ContentSearch;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

class HomeController extends AbstractController
{
    private string $dataPath;
    private ContentLister $contentLister;
    private string $linkCollectionPath;
    /**
     * @var Pdf
     */
    private Pdf $snappy;
    private string $newbeePath;

    /**
     * HomeController constructor.
     * @param string $dataPath
     * @param ContentLister $contentLister
     * @param string $linkCollectionPath
     * @param Pdf $pdf
     * @param string $newbeePath
     */
    public function __construct(
        string $dataPath,
        ContentLister $contentLister,
        string $linkCollectionPath,
        Pdf $pdf,
        string $newbeePath
    ) {
        $this->dataPath = $dataPath;
        $this->contentLister = $contentLister;
        $this->linkCollectionPath = $linkCollectionPath;
        $this->snappy = $pdf;
        $this->newbeePath = $newbeePath;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'name' => 'Übersicht Memos'
        ]);
    }

    /**
     * @Route("/memo/{path}", name="memo")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $path
     * @return Response
     */
    public function memo(string $path): Response
    {
        $content = $this->contentLister->getContentForFile(urldecode($path));

        return $this->render('home/memo.html.twig', [
            'controller_name' => 'Memo',
            'content' => $content
        ]);
    }

    /**
     * @Route("/linksammlung", name="link-collection")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @return Response
     */
    public function linkCollection(): Response
    {
        $content = $this->contentLister->getContentForFile($this->linkCollectionPath);

        return $this->render('home/link-collection.html.twig', [
            'controller_name' => 'Linksammlung',
            'content' => $content,
            'header' => 'pages.linklist'
        ]);
    }

    /**
     * @Route("/neu_dabei", name="newbees")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @return Response
     */
    public function newbee(): Response
    {
        $content = $this->contentLister->getContentForFile($this->newbeePath);

        return $this->render('home/link-collection.html.twig', [
            'controller_name' => 'Neu dabei!?',
            'content' => $content,
            'header' => 'pages.newbee'
        ]);
    }

    /**
     * @Route("/file/{path}", name="file")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $path
     * @return Response
     */
    public function fileDownload(string $path): Response
    {
        $file = new File($this->dataPath . urldecode($path));
        $fileName = urldecode(u($path)->afterLast('/')->toString());
        return $this->file($file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/stammtische/{path}", name="list-stammtische")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $path
     * @return Response
     */
    public function listStammtische(string $path): Response
    {
        $content = $this->contentLister->listContent('/Stammtische/' . $path);
        return $this->render('home/list.html.twig', [
            'name' => 'Stammtische ' . $path,
            'content' => $content
        ]);
    }

    /**
     * @Route("/dokumente/{path}", name="documents")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $path
     * @return Response
     */
    public function listDocuments(string $path = ''): Response
    {
        $content = $this->contentLister->listContent('/Dokumente/' . \urldecode($path));
        return $this->render('home/list.html.twig', [
            'name' => 'Dokumente/' . $path,
            'content' => $content
        ]);
    }

    /**
     * @Route("layout", name="layout")
     * @return Response
     */
    public function layout(): Response
    {
        return $this->render(
            'layout/index.html.twig'
        );
    }

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

        return $this->render('home/search.html.twig', ['name' => 'Suche: ' . $searchText, 'result' => $result, 'searchText' => $searchText]);
    }

    /**
     * @Route("/search_result/{path}", name="search_result")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $path
     * @return Response
     */
    public function searchResult(string $path, ContentSearch  $contentSearch): Response
    {
        $path = u(urldecode($path));
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

    /**
     * @Route("/pdf/{path}", name="pdf")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $path
     * @return Response
     */
    public function toPdf(string $path): Response
    {
        $content = $this->contentLister->getContentForFile(urldecode($path) . '.md');
        $fileName = u(urldecode($path))->afterLast('/')->ensureEnd('.pdf')->toString();
        return new PdfResponse(
            $this->snappy->getOutputFromHtml(
                $this->renderView(
                    'home/simple.html.twig',
                    [
                        'content' => $content->getContent(),
                        'title' => urldecode($path)]
                )
            ),
            $fileName
        );
    }
}
