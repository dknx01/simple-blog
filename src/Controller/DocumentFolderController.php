<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 16.08.20
 */

namespace App\Controller;

use App\Entity\NewFolder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

/**
 * @Route("/admin/dokumente/ordner")
 */
class DocumentFolderController extends AbstractController
{
    private string $dataPath;

    /**
     * DocumentFolderController constructor.
     * @param string $dataPath
     */
    public function __construct(string $dataPath)
    {
        $this->dataPath = $dataPath;
    }

    /**
     * @Route("/list/", name="document_folder_list", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @return Response
     */
    public function list() :Response
    {
        $content =[];
        $folders = (new Finder())->in($this->dataPath)->directories()->sortByName();

        foreach ($folders as $folder) {
            $folderData = [
                'path' => u($folder->getRealPath())->replace($this->dataPath, '')->trimStart('/')->toString(),
                'name' => u($folder->getRealPath())->replace($this->dataPath, '')->afterLast('/')->toString(),
                'name_slug' => u($folder->getRealPath())->replace($this->dataPath, '')->replace(' ', '-')->replace('/', '')->lower()->toString(),
                'empty' => (
                    !(new Finder())->in($folder->getPathname())->files()->name(['*.md', '*.pdf', '*.docx'])->hasResults()
                    && !(new Finder())->in($folder->getPathname())->hasResults()
                ),
                'subFolders' => []
            ];
            if ($folder->getRelativePath() === '') {
                $content[$folderData['name']] = $folderData;
            } else {
                $propertyAccess = PropertyAccess::createPropertyAccessorBuilder()
                    ->enableExceptionOnInvalidIndex()
                    ->getPropertyAccessor();

                $parentString = u($folder->getRelativePath());

                if (!$parentString->containsAny('/')) {
                    $structureParent = $parentString->ensureStart('[')->ensureEnd(']')->append('[subFolders]')->toString();
                } else {
                    $structureParent = u($folder->getRelativePath())->ensureStart('[')->replace('/', '][subFolders][')->ensureEnd(']')->append('[subFolders]')->toString();

                }
                $subFolder = $propertyAccess->getValue($content, $structureParent);
                $subFolder[$folderData['name']] = $folderData;
                $propertyAccess->setValue($content, $structureParent, $subFolder);
            }

        }
        return $this->render('documentsFolder/list.html.twig', ['folders' => $content]);
    }

    /**
     * @Route("/new/{path}", name="document_folder_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param string $path
     * @param Request $request
     * @return Response
     */
    public function add(string $path, Request $request) :Response
    {
        $errors = [];
        $newFolder = new NewFolder();

        if ($request->getMethod() === 'POST'
            && $this->isCsrfTokenValid('new_folder', $request->request->get('_csrf_token'))
        ) {
            $errors = $this->handleNewFolderCreation($request, $errors, $newFolder);
            if (\count($errors) === 0) {
                return $this->redirectToRoute('document_folder_list');
            }
        } else {
            $newFolder->setParent(urldecode($path));
        }
        return $this->render(
            'documentsFolder/new.html.twig',
            [
                'name' => 'Dokumentenordner anlegen',
                'errors' => $errors,
                'newFolder' => $newFolder
            ]
        );
    }

    private function handleNewFolderCreation(Request $request, array $errors, NewFolder $newFolder): array
    {
        $newFolder->setParent($request->request->get('parent'));
        $newFolder->setFoldername($request->request->get('foldername'));

        if ($newFolder->getParent() === '') {
            $errors[] = 'Ordnername darf nicht leer sein';
            return $errors;
        }

        $finder = (new Finder())->in($this->dataPath . '/' . $newFolder->getParent());
        if (\count($finder->directories()->name($newFolder->getFoldername())) > 0) {
            $errors[] = 'Ordner existiert schon';
            return $errors;
        }

        try {
            (new Filesystem())->mkdir($this->dataPath . '/' . $newFolder->getParent() . '/' . $newFolder->getFoldername());
        } catch (IOExceptionInterface $exception) {
            $errors[] = "An error occurred while creating your directory at ".$exception->getPath();
        }

        return $errors;
    }
}