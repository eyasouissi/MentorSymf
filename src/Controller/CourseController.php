<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CourseController extends AbstractController
{
    #[Route('/course', name: 'app_course')]
    public function indexCourses(): Response
    {
        return $this->render('front/course/course.html.twig', [
            'controller_name' => 'CourseController',
        ]);
    }



    #[Route('/courseDetails', name: 'app_details')]
    public function indexDetails(): Response
    {
        return $this->render('front/course/course-details.html.twig', [
            'controller_name' => 'CourseController',
        ]);
    }

// faire appel fel back 

    #[Route('/courseB', name: 'app_courseB')]
    public function indexCoursesB(): Response
    {
        return $this->render('back/course/course.html.twig', [
            'controller_name' => 'CourseController',
        ]);
    }
}
