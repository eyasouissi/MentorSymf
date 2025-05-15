<?php

namespace App\Controller;

use App\Entity\GroupStudent;
use App\Entity\Project;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/group')]
final class GroupController extends AbstractController
{
    #[Route('/', name: 'app_group_index', methods: ['GET'])]
    public function index(GroupRepository $groupRepository): Response
    {
        return $this->render('back/group/index.html.twig', [
            'groupStudents' => $groupRepository->findAll(),
        ]);
    }


    #[Route('/new', name: 'app_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $groupStudent = new GroupStudent();
        $form = $this->createForm(GroupType::class, $groupStudent);
        
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the creator (tutor) but don't add as member
            $groupStudent->setCreatedBy($this->getUser());
            
            // Image handling
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('group_images_directory'),
                        $newFilename
                    );
                    $groupStudent->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'File upload error: '.$e->getMessage());
                    return $this->redirectToRoute('app_group_new');
                }
            }
    
            // Add projects without members
            foreach ($form->get('projects')->getData() as $project) {
                $groupStudent->addProject($project);
            }
    
            // Members will be added later through join button
            $entityManager->persist($groupStudent);
            $entityManager->flush();
    
            $this->addFlash('success', '✅ Group created successfully! Members can now join using the join button.');
            return $this->redirectToRoute('app_group_front');
        }
    
        return $this->render('front/group/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GroupStudent $group, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GroupType::class, $group, [
            'is_edit' => true, // Pass the is_edit option as true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Group updated successfully!');
            return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
        }

        return $this->render('front/group/edit.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }
    #[Route('/{id}', name: 'app_group_delete', methods: ['POST'])]
    public function delete(Request $request, GroupStudent $groupStudent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groupStudent->getId(), $request->request->get('_token'))) {
            $entityManager->remove($groupStudent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_group_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete_all', name: 'app_group_delete_all', methods: ['POST'])]
    public function deleteAllGroups(EntityManagerInterface $entityManager, GroupRepository $groupRepository): RedirectResponse
    {
        $groups = $groupRepository->findAll();

        foreach ($groups as $group) {
            $entityManager->remove($group);
        }

        $entityManager->flush();

        $this->addFlash('success', 'All groups have been deleted.');
        return $this->redirectToRoute('app_group_index');
    }

    #[Route('/front/groups', name: 'app_group_front', methods: ['GET'])]
public function front(GroupRepository $groupRepository): Response
{
    $user = $this->getUser();
    $groups = $groupRepository->findAllWithCreator();
    
    return $this->render('front/group/index.html.twig', [
        'groups' => $groups,
        'current_user' => $user
    ]);
}
    
#[Route('/{id}/join', name: 'group_join', methods: ['POST'])]
public function joinGroup(GroupStudent $group, EntityManagerInterface $em, Request $request): Response
{
    $user = $this->getUser();
    
    if (!$user || !$this->isGranted('ROLE_STUDENT')) {
        return $this->json(['error' => 'Authentication required'], 401);
    }

    // Verify CSRF token from form data
    $submittedToken = $request->request->get('_csrf_token');
    if (!$this->isCsrfTokenValid('join-group', $submittedToken)) {
        return $this->json(['error' => 'Invalid CSRF token'], 403);
    }

    try {
        if (!$group->getMembers()->contains($user)) {
            $group->addMember($user);
            $em->flush();
            return $this->json(['success' => true, 'newCount' => $group->getNbrMembers()]);
        }
        
        return $this->json(['success' => true, 'message' => 'Already a member']);

    } catch (\Exception $e) {
        return $this->json([
            'error' => 'Error joining group: ' . $e->getMessage()
        ], 500);
    }
}
    #[Route('/{id}', name: 'app_group_show', methods: ['GET'])]
    public function show(GroupStudent $group): Response
    {
        return $this->render('front/group/show.html.twig', [
            'group' => $group,
        ]);
    }


   
    
    #[Route('/calendar', name: 'app_calendar_front')]
    public function calendar(EntityManagerInterface $entityManager): Response
    {
        $projects = $entityManager->getRepository(Project::class)->findAll();
    
        // Transformer les projets en événements pour FullCalendar
        $events = [];
        foreach ($projects as $project) {
            $events[] = [
                'title' => $project->getTitre(),
                'start' => $project->getDateLimite()->format('Y-m-d'),
            ];
        }
    
        return $this->render('front/groupfront/index.html.twig', [
            'events' => $events
        ]);
    }
    #[Route('/group/{id}', name: 'group_show', methods: ['GET'])]
    public function showgroup(GroupStudent $group): Response
    {
        return $this->render('front/group/show.html.twig', [
            'group' => $group,
        ]);
    }
    
    
    
 }

