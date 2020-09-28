<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class CacheController extends AbstractController
{
    private CacheClearerInterface $cacheClearer;

    /**
     * @param CacheClearerInterface $globalClearer
     */
    public function __construct(CacheClearerInterface $globalClearer)
    {
        $this->cacheClearer = $globalClearer;
    }

    /**
     * @Route("/cache/leeren/inhalt", name="cache_content_clear")
     * @IsGranted("ROLE_ADMIN")
     * @return Response
     */
    public function contentClear(): Response
    {
        $this->cacheClearer->clear('');
        return $this->render('home/default.html.twig', [
            'title' => 'Inhaltscache leeren',
            'controller_name' => 'Inhaltscache leeren',
            'content' => 'Cache geleert'
        ]);
    }

    /**
     * @Route("/cache/leeren/alles", name="cache_system_clear")
     * @IsGranted("ROLE_ADMIN")
     * @return Response
     */
    public function systemClear(): Response
    {
        $this->cacheClearer->clear('');
        return $this->render('home/default.html.twig', [
            'title' => 'Gesamten Cache leeren',
            'controller_name' => 'Gesamten cache leeren',
            'content' => 'Cache geleert'
        ]);
    }
}