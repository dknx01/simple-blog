<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/benutzer/übersicht", name="user_list")
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function list(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', ['name' => 'Benutzerübersicht', 'users' => $userRepository->findAll()]);
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
            $errors[] = 'Der Benutzer konnte nicht gefunden werden';
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
        return $this->render('user/edit.html.twig', ['name' => 'Benutzer bearbeiten', 'user' => $user, 'errors' => $errors]);
    }

    /**
     * @Route("/benutzer/neu", name="user_new")
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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
                    $errors[] = 'Username ist schon vergeben';
                }
            }
        }
        return $this->render('user/new.html.twig', ['name' => 'Benutzer anlegen', 'user' => $user, 'errors' => $errors]);
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
            $errors[] = 'Der Benutzer konnte nicht gefunden werden';
            return $this->render('user/delete.html.twig', ['name' => 'Benutzer löschen', 'user' => $user, 'errors' => $errors]);
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
            $errors[] = 'Username darf nicht leer sein';
        }
        if ($request->request->has('admin') && $request->request->get('admin') === 1) {
            $user->setRoles(['ROLE_ADMIN']);
        } else {
            $user->setRoles(['ROLE_USER']);
        }
        if ($request->request->get('password') !== '') {
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
        }

        return $errors;
    }
}