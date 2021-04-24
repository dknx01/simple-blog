<?php

namespace App\Controller;

use App\ContentLister\ContentFolderLister;
use App\ContentLister\ContentLister;
use App\Entity\Memo;
use App\Repository\MemoRepository;
use App\Security\File\Sanitizer;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
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
    private MemoRepository $memoRepo;
    private ContentFolderLister $contentFolderLister;

    /**
     * HomeController constructor.
     * @param string $dataPath
     * @param ContentLister $contentLister
     * @param string $linkCollectionPath
     * @param Pdf $pdf
     * @param string $newbeePath
     * @param TranslatorInterface $translator
     * @param MemoRepository $memoRepository
     */
    public function __construct(
        string $dataPath,
        ContentLister $contentLister,
        string $linkCollectionPath,
        Pdf $pdf,
        string $newbeePath,
        TranslatorInterface $translator,
        MemoRepository $memoRepository,
        ContentFolderLister $contentFolderLister
    ) {
        $this->dataPath = $dataPath;
        $this->contentLister = $contentLister;
        $this->linkCollectionPath = $linkCollectionPath;
        $this->snappy = $pdf;
        $this->newbeePath = $newbeePath;
        $this->translator = $translator;
        $this->memoRepo = $memoRepository;
        $this->contentFolderLister = $contentFolderLister;
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
        $content = $this->contentFolderLister->getContent(urldecode($path));

        return $this->render('home/memo.html.twig', [
            'controller_name' => $this->translator->trans('memo.headline', [], 'pages'),
            'content' => $content
        ]);
    }

    /**
     * @Route("/memo_short/{id}", name="memo_short_link")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param string $id
     * @return Response
     */
    public function shortLink(string $id): Response
    {
        /** @var Memo $memo */
        $memo = $this->memoRepo->findOneBy(['uuid' => $id]);
        if ($memo === null) {
            return $this->redirectToRoute('home');
        }
        return $this->redirectToRoute(
            'memo',
            [
                'path' => urlencode($memo->getLocation() . '/' . $memo->getFileName())
            ]
        );
    }

    /**
     * @Route("/linksammlung", name="link-collection")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @return Response
     */
    public function linkCollection(): Response
    {
        $content = $this->contentFolderLister->getContent($this->linkCollectionPath);

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
        $content = $this->contentFolderLister->getContent($this->newbeePath);

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
        $path = urldecode($path);
        $memo = $this->memoRepo->findMemo($path);
        $file = new File($this->dataPath . '/' . $memo['uuid'] . '.' . $memo['extension']);
        $fileName = $memo['file_name'];
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
        $path = urldecode($path);
        $content = $this->contentFolderLister->getFolder('/Stammtische/' . $path);
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
        $path = urldecode($path);
        $location =  $path === '' ? '/Dokumente' : $path;
        $content = $this->contentFolderLister->getFolder($location);
        $translationsPath = strpos($path, '/') === 0 ? substr($path, 1) : $path;
        return $this->render('home/list.html.twig', [
            'name' => $this->translator->trans('document.header', ['%path%' => $translationsPath], 'pages'),
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
//    public function toPdf(string $path): Response
//    {
//        $path = urldecode($path);
//        $content = $this->contentFolderLister->getContent($path);
//        $fileName = u($content['file_name'])->replace('.md', '.pdf')->toString();
//        return new PdfResponse(
//            $this->snappy->getOutputFromHtml(
//                $this->renderView(
//                    'home/simple.html.twig',
//                    [
//                        'content' => $content['content'],
//                        'title' => $content['title']
//                    ]
//                )
//            ),
//            $fileName
//        );
//    }
}
