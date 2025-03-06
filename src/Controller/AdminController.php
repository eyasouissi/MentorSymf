<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        // Fetch all users from the database, excluding admins
        $usersQuery = $entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery();

        // Paginate the results
        $users = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            7
        );

        return $this->render('back/admin/admin.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/edit/{id}', name: 'admin_edit_user')]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Manually set roles since the form field is not mapped
            $selectedRole = $form->get('roles')->getData();
            $user->setRoles([$selectedRole]);

            // Check if a new password is set and hash it
            if ($user->getPlainPassword()) {
                $hashedPassword = $userPasswordHasher->hashPassword($user, $user->getPlainPassword());
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');

            return $this->redirectToRoute('admin');
        }

        return $this->render('back/admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'admin_delete_user', methods: ['POST'])]
    public function delete(
        User $user,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/user/restrict/{id}', name: 'admin_restrict_user', methods: ['POST'])]
    public function restrict(
        User $user,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse {
        // Log the CSRF token and user ID for debugging
        error_log('CSRF token received: ' . $request->request->get('_token'));
        error_log('Expected CSRF token: restrict' . $user->getId());
    
        if ($this->isCsrfTokenValid('restrict' . $user->getId(), $request->request->get('_token'))) {
            // Toggle the isRestricted property
            $user->setIsRestricted(!$user->getIsRestricted());
            $entityManager->persist($user);
            $entityManager->flush();
    
            return $this->json([
                'success' => true,
                'isRestricted' => $user->getIsRestricted(),
                'message' => $user->getIsRestricted() ? 'User has been restricted successfully.' : 'User has been unrestricted successfully.'
            ]);
        }
    
        return $this->json([
            'success' => false,
            'message' => 'Invalid CSRF token.'
        ], 400);
    }
}