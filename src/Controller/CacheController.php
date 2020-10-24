<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class CacheController extends AbstractController
{
    private CacheClearerInterface $cacheClearer;
    private TranslatorInterface $translator;

    /**
     * @param CacheClearerInterface $globalClearer
     * @param TranslatorInterface $translator
     */
    public function __construct(CacheClearerInterface $globalClearer, TranslatorInterface $translator)
    {
        $this->cacheClearer = $globalClearer;
        $this->translator = $translator;
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
            'title' => $this->translator->trans('cache.delete_content_cache', [], 'pages'),
            'controller_name' => $this->translator->trans('cache.delete_content_cache', [], 'pages'),
            'content' => $this->translator->trans('cache.cleared', [], 'pages')
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
            'title' => $this->translator->trans('cache.delete_whole_cache', [], 'pages'),
            'controller_name' => $this->translator->trans('cache.delete_whole_cache', [], 'pages'),
            'content' => $this->translator->trans('cache.cleared', [], 'pages')
        ]);
    }
}