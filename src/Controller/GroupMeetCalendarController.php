<?php
namespace App\Controller;

use App\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupMeetCalendarController extends AbstractController
{
    #[Route('/group-meet-calendar', name: 'group_meet_calendar')]
    public function index(GroupRepository $groupRepository): Response
    {
        // Récupérer tous les groupes avec leurs dates de réunion
        $groups = $groupRepository->findAll();

        // Préparer les données pour FullCalendar
        $events = [];
        foreach ($groups as $group) {
            if ($group->getDateMeet()) {
                // Générer un lien vers Jitsi (vous pouvez personnaliser cette URL)
                $meetUrl = 'https://meet.jit.si/' . urlencode('Meet-with-' . $group->getNomGroup());

                $events[] = [
                    'title' => 'Meet with ' . $group->getNomGroup() . ' at ' . $group->getDateMeet()->format('H:i'),
                    'start' => $group->getDateMeet()->format('Y-m-d\TH:i:s'), // Format ISO 8601
                    'url' => $meetUrl,  // Lien vers Jitsi
                ];
            }
        }

        // Passer les données à la vue
        return $this->render('front/group_meet_calendar/index.html.twig', [
            'events' => $events,
        ]);
    }
}
