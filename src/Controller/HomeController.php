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
use Symfony\Contracts\Translation\TranslatorInterface;
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
    private TranslatorInterface $translator;

    /**
     * HomeController constructor.
     * @param string $dataPath
     * @param ContentLister $contentLister
     * @param string $linkCollectionPath
     * @param Pdf $pdf
     * @param string $newbeePath
     * @param TranslatorInterface $translator
     */
    public function __construct(
        string $dataPath,
        ContentLister $contentLister,
        string $linkCollectionPath,
        Pdf $pdf,
        string $newbeePath,
        TranslatorInterface $translator
    ) {
        $this->dataPath = $dataPath;
        $this->contentLister = $contentLister;
        $this->linkCollectionPath = $linkCollectionPath;
        $this->snappy = $pdf;
        $this->newbeePath = $newbeePath;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'name' => $this->translator->trans('memo.overview', [], 'pages')
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
            'controller_name' => $this->translator->trans('memo.headline', [], 'pages'),
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
            'controller_name' => $this->translator->trans('linklist.name', [], 'pages'),
            'content' => $content,
            'header' => $this->translator->trans('linklist.name', [], 'pages')
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
            'controller_name' => $this->translator->trans('newbee', [], 'pages'),
            'content' => $content,
            'header' => $this->translator->trans('newbee', [], 'pages')
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
            'name' => $this->translator->trans('stammtisch', ['%path%' => $path], 'pages'),
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
            'name' => $this->translator->trans('document.header', ['%path%' => $path], 'pages'),
            'content' => $content
        ]);
    }

    /**
     * @Route("layout", name="layout")
     * @IsGranted("ROLE_ADMIN")
     * @return Response
     */
    public function layout(): Response
    {
        return $this->render(
            'layout/index.html.twig'
        );
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
