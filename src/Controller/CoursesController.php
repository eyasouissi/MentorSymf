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

class CoursesController extends AbstractController
{
    private $entityManager;

    // Injection du gestionnaire d'entités dans le constructeur
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
        ]);
    }

    // Page de succès après la création du cours
    #[Route('/course/success', name: 'course_success')]
    public function courseSuccess(): Response
    {
        return $this->render('front/courses/course_success.html.twig');
    }
}
