<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 13.08.20
 */

namespace App\Controller;

use App\Entity\DirectoryContent;
use App\Entity\MemoEdit;
use App\Form\MemoEditType;
use App\Form\MemoPdf;
use App\MarkdownContent\MarkdownReader;
use App\Repository\MemoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

/**
 * @Route("/admin")
 */
class MemoUpload extends AbstractController
{
    private string $dataPath;

    /**
     * @param string $dataPath
     */
    public function __construct(string $dataPath)
    {
        $this->dataPath = $dataPath;
    }

    /**
     * @Route("/upload", name="memo_upload")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param SluggerInterface $slugger
     * @return Response
     */
    public function upload(Request $request, SluggerInterface $slugger): Response
    {
        $pdf = new MemoPdf();
        $form = $this->createForm(MemoPdf::class, $pdf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $pdfFile */
            $pdfFile = $form->get('pdf')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($pdfFile) {
                $originalFilename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '.' . $pdfFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $storagePath = $this->$this->dataPath . '/' . $form->get('type')->getData();
                    $pdfFile->move(
                        $storagePath,
                        $newFilename
                    );
                } catch (FileException $e) {
                }
            }
        }
        return $this->render('memo/upload.html.twig', [
            'name' => 'Upload Memos',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{path}", name="memo-edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param MarkdownReader $markdownReader
     * @param MemoRepository $repository
     * @param ?string $path
     * @return Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function memoEdit(
        Request $request,
        MarkdownReader $markdownReader,
        MemoRepository $repository,
        $path = null
    ): Response
    {
        $errors = [];
        if ($path === null && $request->getMethod() === 'GET') {
            $errors[] = 'No valid path';
        }

        $memoEdit = new MemoEdit();

        if ($request->getMethod() === 'GET') {
            $filePath = $this->dataPath . '/' . urldecode($path);
            $memoEdit->setContent(file_get_contents($filePath));
            $memoEdit->setPath(
                u($filePath)->replace($this->dataPath, '')
            );
        }

        $form = $this->createForm(MemoEditType::class, $memoEdit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $repository->updateMemo($memoEdit);
                $markdownReader->refreshContent($filePath);
            } catch (\Exception $exception) {
                $errors[] = $this->sanitizeErrorMessage($exception);
            }
        }

        return $this->render('memo/edit.html.twig', [
            'memoEdit' => $memoEdit,
            'form' => $form->createView(),
            'errors' => $errors
        ]);
    }

    /**
     * @Route("/loeschen/{path}/{force}", name="memo-delete", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param string $path
     * @param bool $force
     * @return Response
     */
    public function delete(string $path, bool $force = false): Response
    {
        if ($force === true) {
            unlink($this->dataPath . '/' . urldecode($path));
            return  $this->redirectToRoute('memo-edit-list');
        }
        return $this->render('memo/delete.html.twig', ['name' => 'Datei Löschen', 'filename' => urldecode($path)]);
    }

    /**
     * @Route("/edit_list", name="memo-edit-list", methods={"GET"})
     * @return Response
     */
    public function memoEditList(): Response
    {
        $content = [];

        foreach ((new Finder())->in($this->dataPath)->directories()->sortByName() as $folder) {
            $fc = (new Finder())->in($folder->getPathname())->files()->name(['*.md']);
            if ($fc->hasResults())
            {
                foreach ($fc as $fileInfo)
                {
                    $path = $fileInfo->getPath() . '/' . $fileInfo->getFilename();
                    $content[] = u($path)->replace($this->dataPath, '')->trimStart('/')->toString();
                }
            }
        }

        return $this->render('memo/edit-list.html.twig', [
            'name' => 'Übersicht Inhalte',
            'content' => $content
        ]);
    }

    /**
     * @param \Exception $exception
     * @return string|\Symfony\Component\String\AbstractString
     */
    private function sanitizeErrorMessage(\Exception $exception)
    {
        $sanitize = [
            $this->dataPath,
            'failed to open stream:'
        ];

        $message = u($exception->getMessage());
        foreach ($sanitize as $item) {
            $message = $message->replace($item, '');
        }

        $message = $message->replaceMatches('/file_put_contents\((.*)\):/', static function($match) {
            return $match[1];
        });
        return $message->trim()->toString();
    }
}