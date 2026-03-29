<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/events', name: 'event_list')]
    public function index(EventRepository $repo, ReservationRepository $resRepo): Response
    {
        $events = $repo->findAllEvents();

        $reservationsCount = [];
        foreach ($events as $event) {
            $reservationsCount[$event->getId()] = $resRepo->count(['event_id' => $event]);
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'reservationsCount' => $reservationsCount
        ]);
    }

    #[Route('/event/{id}', name: 'event_details')]
    public function details(int $id, EventRepository $repo, ReservationRepository $resRepo): Response
    {
        $event = $repo->findEventById($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        $reservationsCount = [];
        $reservationsCount[$event->getId()] = $resRepo->count(['event_id' => $event]);

        return $this->render('event/details.html.twig', [
            'event' => $event,
            'reservationsCount' => $reservationsCount,
        ]);
    }
}
