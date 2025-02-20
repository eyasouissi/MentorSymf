<?php
namespace App\Controller\user;
use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;




#[Route('/profile')]
#[IsGranted('ROLE_USER')] // Seuls les utilisateurs connectés peuvent accéder
class ProfileController extends AbstractController
{
    #[Route('/', name: 'profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/upload-pfp', name: 'profile_upload_pfp', methods: ['POST'])]
public function uploadProfilePicture(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Access denied');
    }

    $file = $request->files->get('pfp');

    if ($file instanceof UploadedFile) {
        $newFilename = uniqid().'.'.$file->guessExtension(); 

        $uploadDir = $this->getParameter('pfp_upload_directory');

        try {
            $file->move($uploadDir, $newFilename);

            $user->setPfp($newFilename);
            $entityManager->flush(); // ✅ Corrigé

            $this->addFlash('success', 'Profile picture updated successfully!');
        } catch (FileException $e) {
            $this->addFlash('error', 'An error occurred while uploading the profile picture.');
        }
    }

    return $this->redirectToRoute('profile');
}



#[Route('/edit', name: 'profile_edit')]
public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Access denied');
    }

    $form = $this->createFormBuilder($user)
        ->add('name', TextType::class, ['label' => 'Nom'])
        ->add('email', EmailType::class, ['label' => 'Email'])
        ->add('bio', TextareaType::class, ['required' => false, 'label' => 'Bio'])
        ->add('speciality', TextType::class, ['required' => false, 'label' => 'Spécialité'])
        ->add('pfp', FileType::class, [
            'label' => 'Photo de profil',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, JPEG, PNG).',
                ])
            ],
        ])
        ->add('diplome', FileType::class, [
            'label' => 'Diplôme (PDF ou Word)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                    'mimeTypesMessage' => 'Seuls les fichiers PDF et Word sont acceptés.',
                ])
            ],
        ])
        ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $uploadDir = $this->getParameter('pfp_upload_directory'); // Dossier des fichiers

        // Upload de la photo de profil
        $pfpFile = $form->get('pfp')->getData();
        if ($pfpFile) {
            $newPfpFilename = uniqid().'.'.$pfpFile->guessExtension();
            $pfpFile->move($uploadDir, $newPfpFilename);
            $user->setPfp($newPfpFilename);
        }

        // Upload du diplôme
        $diplomeFile = $form->get('diplome')->getData();
        if ($diplomeFile) {
            $newDiplomeFilename = uniqid().'.'.$diplomeFile->guessExtension();
            $diplomeFile->move($uploadDir, $newDiplomeFilename);
            $user->setDiplome($newDiplomeFilename);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Profil mis à jour avec succès !');

        return $this->redirectToRoute('profile');
    }

    return $this->render('user/edit_profile.html.twig', [
        'form' => $form->createView(),
    ]);
}

}
