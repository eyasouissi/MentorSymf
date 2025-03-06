<?php
namespace App\Controller;

use App\Entity\Courses;
use App\Entity\Category;
use App\Entity\Level;
use App\Entity\File;
use App\Form\CoursesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\RatingType;
use App\Entity\Rating;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CoursesRepository; // ✅ Vérifie bien cet import !
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\RatingRepository;




class CoursesController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/create_course/{id}', name: 'create_course')]
    public function create(int $id, Request $request): Response
    {
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->find($id);
    
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
    
        $course = new Courses();
        $course->setCategory($category);
    
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
    
        $form = $this->createForm(CoursesType::class, $course, [
            'categories' => $categories,
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $course = $form->getData();
    
            // 🔹 Vérifier si l'utilisateur est ROLE_TUTOR et stocker son nom
            $user = $this->getUser();
            if ($user && in_array('ROLE_TUTOR', $user->getRoles())) {
                $course->setTutorName($user->getName());
            }
    
            // Récupérer le nombre de niveaux sélectionné
            $numberOfLevels = $form->get('numberOfLevels')->getData();
    
            // Création dynamique des niveaux
            for ($i = 1; $i <= $numberOfLevels; $i++) {
                $level = new Level();
                $level->setName("Niveau $i");
                $level->setCourse($course);
                $this->entityManager->persist($level);
            }
    
            // Gérer les fichiers téléchargés
            foreach ($course->getLevels() as $level) {
                foreach ($level->getFiles() as $file) {
                    if ($file->getFile()) {
                        $fileName = $this->handleFileUpload($file);
                        $file->setFileName($fileName);
                        $this->entityManager->persist($file);
                    }
                }
            }
    
            // Sauvegarde en base de données
            $this->entityManager->persist($course);
            $this->entityManager->flush();
    
            return $this->redirectToRoute('course_details', ['id' => $course->getId()]);
        }
    
        return $this->render('front/courses/create_course.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }
    
   
   


// Afficher les détails d'un cours
#[Route('/courseDetails/{id}', name: 'course_details')]
public function showCourseDetails(int $id): Response
{
    // Récupérer le cours depuis la base de données
    $course = $this->entityManager->getRepository(Courses::class)->find($id);

    if (!$course) {
        throw $this->createNotFoundException('Course not found');
    }

    // Récupérer la note de l'utilisateur actuel (s'il est connecté)
    $userRating = null;
    $user = $this->getUser();
    if ($user) {
        $ratingRepository = $this->entityManager->getRepository(Rating::class);
        $userRating = $ratingRepository->findOneBy([
            'course' => $course,
            'user' => $user,
        ]);
    }

    // Afficher les fichiers associés à chaque niveau
    $levelsWithFiles = [];
    foreach ($course->getLevels() as $level) {
        $levelsWithFiles[] = [
            'level' => $level,
            'files' => $level->getFiles(),
        ];
    }

    // Passer les informations au template
    return $this->render('front/courses/course_details.html.twig', [
        'course' => $course,
        'levelsWithFiles' => $levelsWithFiles,
        'user_rating' => $userRating ? $userRating->getRating() : null, // Passer la note de l'utilisateur
    ]);
}


    // Page de succès après la création du cours
    #[Route('/course/success', name: 'course_success')]
    public function courseSuccess(): Response
    {
        return $this->render('front/courses/course_success.html.twig');
    }


    #[Route('/course/{id}/rate', name: 'rate_course', methods: ['POST'])]
    public function rateCourse(Request $request, Courses $course): JsonResponse
    {
        $rating = $request->request->get('rating'); // Récupérer la note envoyée par AJAX
        
        if (!$rating || !in_array($rating, [1, 2, 3, 4, 5])) {
            return $this->json(['error' => 'Note invalide'], 400); // Retourner une erreur si la note est invalide
        }
    
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté pour noter ce cours.'], 401);
        }
    
        // Vérifier si l'utilisateur a déjà noté ce cours
        $ratingRepository = $this->entityManager->getRepository(Rating::class);
        $existingRating = $ratingRepository->findOneBy([
            'course' => $course,
            'user' => $user,
        ]);
    
        if ($existingRating) {
            // Mettre à jour la note existante
            $existingRating->setRating($rating);
        } else {
            // Créer une nouvelle note
            $ratingEntity = new Rating();
            $ratingEntity->setCourse($course);
            $ratingEntity->setUser($user);
            $ratingEntity->setRating($rating);
            $this->entityManager->persist($ratingEntity);
        }
    
        $this->entityManager->flush();
    
        // Déterminer le message et la couleur en fonction de la note
        $message = '';
        $color = '';
    
        switch ($rating) {
            case 1:
                $message = 'If something went wrong, please contact the Mentor Team';
                $color = 'danger'; // Rouge (classe Bootstrap)
                break;
            case 2:
                $message = 'Oh no, this is going to be bad';
                $color = 'warning'; // Orange (classe Bootstrap)
                break;
            case 3:
                $message = 'Oh oh, is there a problem?';
                $color = 'yellow'; // Jaune (classe personnalisée)
                break;
            case 4:
                $message = 'Hope you enjoyed your class with us';
                $color = 'success'; // Vert (classe Bootstrap)
                break;
            case 5:
                $message = 'Good job, you did great!!!';
                $color = 'violet'; // Violet (classe personnalisée)
                break;
            default:
                $message = 'Merci pour votre note !';
                $color = 'primary'; // Couleur par défaut (classe Bootstrap)
                break;
        }
    
        return $this->json([
            'success' => true,
            'message' => $message,
            'color' => $color, // Renvoyer la classe CSS correspondante
        ]);
    }




    //////////////////////backend//////////////////////////


    // src/Controller/CoursesController.php

    #[Route('Admin/create_course', name: 'CourseBack')]
    public function createBack(Request $request): Response
    {
        $course = new Courses();
    
        // Récupérer toutes les catégories pour le formulaire
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
    
        // Création du formulaire avec la liste des catégories
        $form = $this->createForm(CoursesType::class, $course, [
            'categories' => $categories,
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la catégorie sélectionnée
            $selectedCategory = $form->get('category')->getData();
            $course->setCategory($selectedCategory);
    
            // Récupérer le nombre de niveaux sélectionné
            $numberOfLevels = $form->get('numberOfLevels')->getData();
    
            // Création dynamique des niveaux
            for ($i = 1; $i <= $numberOfLevels; $i++) {
                $level = new Level();
                $level->setName("Niveau $i");
                $level->setCourse($course);
                $this->entityManager->persist($level);
            }
    
            // Persister le cours
            $this->entityManager->persist($course);
            $this->entityManager->flush();
    
            return $this->redirectToRoute('course_details_back', ['id' => $course->getId()]);
        }
    
        return $this->render('back/course/create_course.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    



    #[Route('/admin/courses', name: 'admin_courses_list')]
public function listCourses(Request $request, CoursesRepository $courseRepository): Response
{
    // Récupérer les paramètres de recherche et de filtrage
    $searchTerm = $request->query->get('search');
    $categoryId = $request->query->get('category');
    $sortField = $request->query->get('sort', 'id');
    $sortDirection = $request->query->get('direction', 'ASC');

    // Convertir categoryId en entier (ou null si vide ou non numérique)
    $categoryId = $categoryId !== null && is_numeric($categoryId) ? (int)$categoryId : null;

    // Récupérer les cours filtrés et triés
    $courses = $courseRepository->searchAndFilter($searchTerm, $categoryId, $sortField, $sortDirection);

    // Si c'est une requête AJAX, renvoyer uniquement le tableau des cours
    if ($request->isXmlHttpRequest()) {
        return $this->render('back/course/courses_list.html.twig', [
            'courses' => $courses,
            'is_ajax' => true, // Indicateur pour savoir si c'est une requête AJAX
        ]);
    }

    // Récupérer toutes les catégories pour le filtre
    $categories = $courseRepository->findAllCategories();

    return $this->render('back/course/courses_list.html.twig', [
        'courses' => $courses,
        'categories' => $categories,
        'searchTerm' => $searchTerm,
        'selectedCategory' => $categoryId,
        'sortField' => $sortField,
        'sortDirection' => $sortDirection,
        'is_ajax' => false, // Indicateur pour savoir si c'est une requête AJAX
    ]);
}

#[Route('/Admin/courseDetails/{id}', name: 'course_details_back')]
public function courseDetailsBack(int $id): Response
{
    $course = $this->entityManager->getRepository(Courses::class)->find($id);

    if (!$course) {
        throw $this->createNotFoundException('Course not found');
    }

    return $this->render('back/course/course-details.html.twig', [
        'course' => $course,
    ]);
}

#[Route('/Admin/course/delete/{id}', name: 'course_delete')]
public function deleteCourse(int $id): Response
{
    $course = $this->entityManager->getRepository(Courses::class)->find($id);

    if (!$course) {
        throw $this->createNotFoundException('Course not found');
    }

    $this->entityManager->remove($course);
    $this->entityManager->flush();

    return $this->redirectToRoute('admin_courses_list');
}

#[Route('/Admin/course/edit/{id}', name: 'course_edit')]
public function editCourse(int $id, Request $request): Response
{
    $course = $this->entityManager->getRepository(Courses::class)->find($id);

    if (!$course) {
        throw $this->createNotFoundException('Course not found');
    }

    // Création du formulaire avec les catégories et l'option premium
    $form = $this->createForm(CoursesType::class, $course, [
        'categories' => $this->entityManager->getRepository(Category::class)->findAll(),
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifier si l'admin a coché "Premium"
        $isPremium = $form->get('isPremium')->getData();
        $course->setIsPremium($isPremium);

        $this->entityManager->flush();  // Sauvegarde les modifications
        return $this->redirectToRoute('admin_courses_list');  // Redirige vers la liste des cours
    }

    return $this->render('back/course/edit_course.html.twig', [
        'form' => $form->createView(),
        'course' => $course,
    ]);
}





    // src/Controller/CoursesController.php

// src/Controller/CoursesController.php

#[Route('/admin/courses/export-pdf', name: 'admin_courses_export_pdf')]
public function exportPdf(EntityManagerInterface $entityManager): Response
{
    // Récupérer les cours depuis la base de données
    $courses = $entityManager
        ->getRepository(Courses::class)
        ->findAll();

    // Options pour Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Activer le support des images distantes

    // Initialiser Dompdf
    $dompdf = new Dompdf($options);

    // Rendre le template Twig en HTML
    $html = $this->renderView('back/course/courses_pdf.html.twig', [
        'courses' => $courses,
    ]);

    // Charger le HTML dans Dompdf
    $dompdf->loadHtml($html);

    // Définir la taille et l'orientation du papier
    $dompdf->setPaper('A4', 'portrait');

    // Rendre le PDF
    $dompdf->render();

    // Générer le fichier PDF et le renvoyer en réponse
    return new Response(
        $dompdf->output(),
        Response::HTTP_OK,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="courses.pdf"',
        ]
    );
}





/*
        #[Route('/courses/statistics', name: 'courses_statistics')]
        public function stats(RatingRepository $ratingRepository, EntityManagerInterface $entityManager)
        {
            // Récupérer tous les cours
            $courses = $entityManager->getRepository(Courses::class)->findAll();
            
            $courseData = [];
            foreach ($courses as $course) {
                // Récupérer les évaluations pour chaque cours
                $ratings = $ratingRepository->findBy(['course' => $course]);
        
                // Calcul de la moyenne des évaluations
                $total = count($ratings);
                $sum = array_sum(array_map(fn($r) => $r->getRating(), $ratings));
                $average = $total > 0 ? $sum / $total : 0;
        
                // Comptage des votes par note
                $ratingCounts = array_fill(1, 5, 0);
                foreach ($ratings as $rating) {
                    $ratingCounts[$rating->getRating()]++;
                }
        
                // Rassembler les données du cours
                $courseData[] = [
                    'id' => $course->getId(),
                    'name' => $course->getTitle(),  // Assurez-vous que "getTitle()" existe dans l'entité Courses
                    'averageRating' => $average,
                    'ratings' => $ratingCounts
                ];
            }
        
            // Passer les données à la vue
            return $this->render('back/course/statistics.html.twig', [
                'courses' => $courseData
            ]);
        }*/
      
        
        #[Route('/courses/statistics', name: 'courses_statistics')]
public function stats(RatingRepository $ratingRepository, EntityManagerInterface $entityManager)
{
    // Récupérer tous les cours
    $courses = $entityManager->getRepository(Courses::class)->findAll();
    
    $courseData = [];
    $premiumCount = 0;
    $normalCount = 0;

    foreach ($courses as $course) {
        // Récupérer les évaluations pour chaque cours
        $ratings = $ratingRepository->findBy(['course' => $course]);

        // Calcul de la moyenne des évaluations
        $total = count($ratings);
        $sum = array_sum(array_map(fn($r) => $r->getRating(), $ratings));
        $average = $total > 0 ? $sum / $total : 0;

        // Comptage des votes par note
        $ratingCounts = array_fill(1, 5, 0);
        foreach ($ratings as $rating) {
            $ratingCounts[$rating->getRating()]++;
        }

        // Rassembler les données du cours
        $courseData[] = [
            'id' => $course->getId(),
            'name' => $course->getTitle(),
            'averageRating' => $average,
            'ratings' => $ratingCounts,
            'isPremium' => $course->isPremium() // Ajout de l'information premium
        ];

        // Comptage des cours premium et non premium
        if ($course->isPremium()) {
            $premiumCount++;
        } else {
            $normalCount++;
        }
    }

    // Passer les données à la vue
    return $this->render('back/course/statistics.html.twig', [
        'courses' => $courseData,
        'premiumCount' => $premiumCount,
        'normalCount' => $normalCount
    ]);
}



}