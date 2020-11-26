<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Roles;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class UserController extends AbstractController
{
    private TranslatorInterface $translator;

    /**
     * UserController constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/benutzer/übersicht", name="user_list")
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function list(UserRepository $userRepository): Response
    {
        return $this->render(
            'user/list.html.twig',
            [
                'name' => $this->translator->trans('user.overview', [], 'pages'),
                'users' => $userRepository->findAll()
            ]
        );
    }

    /**
     * @Route("/benutzer/bearbeiten/{id}", name="user_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param int $id
     * @param UserRepository $userRepository
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function edit(int $id, UserRepository $userRepository, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $errors = [];
        $user = $userRepository->find($id);
        if ($user === null) {
            $errors[] = $this->translator->trans('user.errors.user not found', [], 'pages');
        }

        if ($user instanceof User && $request->getMethod() === 'POST'
            && $this->isCsrfTokenValid('edit_user', $request->request->get('_csrf_token'))
        ) {
            $errors = $this->handleUserUpdate($request, $user, $passwordEncoder, $errors);

            if (\count($errors) === 0) {
                $userRepository->update($user);
                return $this->redirectToRoute('user_list');
            }
        }
        return $this->render(
            'user/edit.html.twig',
            [
                'name' => $this->translator->trans('user.edit', [], 'pages'),
                'user' => $user,
                'errors' => $errors
            ]
        );
    }

    /**
     * @Route("/benutzer/neu", name="user_new")
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function new(UserRepository $userRepository, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $errors = [];
        $user = new User();

        if ($user instanceof User && $request->getMethod() === 'POST'
            && $this->isCsrfTokenValid('new_user', $request->request->get('_csrf_token'))
        ) {
            $errors = $this->handleUserUpdate($request, $user, $passwordEncoder, $errors);
            if (\count($errors) === 0) {
                $user->setUsername($request->request->get('username'));
                try {
                    $user = $userRepository->save($user);
                    return $this->redirectToRoute('user_list');
                } catch (UniqueConstraintViolationException $exception) {
                    $errors[] = $this->translator->trans('user.errors.user already exists', [], 'pages');
                }
            }
        }
        return $this->render(
            'user/new.html.twig',
            [
                'name' => $this->translator->trans('user.new', [], 'pages'),
                'user' => $user,
                'errors' => $errors
            ]
        );
    }

    /**
     * @Route("/benutzer/loeschen/{id}", name="user_delete", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param int $id
     * @param UserRepository $userRepository
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function delete(int $id, UserRepository $userRepository, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $errors = [];
        $user = $userRepository->find($id);
        if ($user === null) {
            $errors[] = $this->translator->trans('user.errors.user not found', [], 'pages');
            return $this->render(
                'user/delete.html.twig',
                [
                    'name' => $this->translator->trans('user.delete', [], 'pages'),
                    'user' => $user,
                    'errors' => $errors
                ]
            );
        }

        $userRepository->delete($user);
        return $this->redirectToRoute('user_list');
    }

    /**
     * @param Request $request
     * @param User $user
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param array $errors
     * @return array
     */
    private function handleUserUpdate(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder, array $errors): array
    {
        if ($request->request->get('username') === '') {
            $errors[] = $this->translator->trans('user.errors.name not empty', [], 'pages');
        }

        $roles = [];
        if ($this->isNormalUser($request)) {
            $roles[] = Roles::ROLE_USER;
        } else {
            if ($request->request->has('admin') && $request->request->get('admin') === '1') {
                $roles[] = Roles::ROLE_ADMIN;
            }
            if ($request->request->has('editor') && $request->request->get('editor') === '1') {
                $roles[] = Roles::ROLE_EDITOR;
            }
            if ($request->request->has('ov') && $request->request->get('ov') === '1') {
                $roles[] = Roles::ROLE_OV_MEMBER;
            }
        }

        $user->setRoles($roles);

        if ($request->request->get('password') !== '') {
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
        }

        return $errors;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isNormalUser(Request $request): bool
    {
        return
            !$request->request->has('admin')
            && !$request->request->has('editor')
            && !$request->request->has('ov')
            ;
    }
}