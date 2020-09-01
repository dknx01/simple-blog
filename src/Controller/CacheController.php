<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class CacheController extends AbstractController
{
    private string $cacheDir;

    /**
     * @param string $cacheDir
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @Route("/cache/leeren/inhalt", name="cache_content_clear")
     * @IsGranted("ROLE_ADMIN")
     * @param Filesystem $filesystem
     * @return Response
     */
    public function contentClear(Filesystem $filesystem): Response
    {
        $filesystem->remove($this->cacheDir . '/pools/');
        return $this->render('home/default.html.twig', [
            'title' => 'Inhaltscache leeren',
            'controller_name' => 'Inhaltscache leeren',
            'content' => 'Cache geleert'
        ]);
    }

    /**
     * @Route("/cache/leeren/alles", name="cache_system_clear")
     * @IsGranted("ROLE_ADMIN")
     * @param Filesystem $filesystem
     * @return Response
     */
    public function systemClear(Filesystem $filesystem): Response
    {
        $filesystem->remove($this->cacheDir . '/');
        return $this->render('home/default.html.twig', [
            'title' => 'Gesamten Cache leeren',
            'controller_name' => 'Gesamten cache leeren',
            'content' => 'Cache geleert'
        ]);
    }
}