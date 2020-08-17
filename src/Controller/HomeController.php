<?php

namespace App\Controller;

use App\ContentLister\ContentLister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
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
     * HomeController constructor.
     * @param string $dataPath
     * @param ContentLister $contentLister
     * @param string $linkCollectionPath
     */
    public function __construct(string $dataPath, ContentLister $contentLister, string $linkCollectionPath)
    {
        $this->dataPath = $dataPath;
        $this->contentLister = $contentLister;
        $this->linkCollectionPath = $linkCollectionPath;
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
            'content' => $content
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
        $content = $this->contentLister->listContent('/Dokumente/' . $path);
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
}
