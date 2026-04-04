<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'admin_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method should not be reached.');
    }

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EventRepository $eventRepo, ReservationRepository $resRepo): Response
    {
        $events = $eventRepo->findAllEvents();
        $totalReservations = count($resRepo->findAllReservations());

        $now = new \DateTime();
        $upcomingEvents = array_filter($events, fn(Event $e) => $e->getDate() > $now);

        $reservationsCount = [];
        foreach ($events as $event) {
            $reservationsCount[$event->getId()] = $resRepo->count(['event_id' => $event]);
        }

        return $this->render('admin/dashboard.html.twig', [
            'events' => $events,
            'totalEvents' => count($events),
            'totalReservations' => $totalReservations,
            'upcomingEvents' => count($upcomingEvents),
            'reservationsCount' => $reservationsCount,
        ]);
    }

    #[Route('/event/new', name: 'admin_event_new')]
    public function newEvent(Request $request, EventRepository $eventRepo, SluggerInterface $slugger): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = $this->uploadImage($imageFile, $slugger);
                if ($newFilename) {
                    $event->setImage($newFilename);
                }
            }

            $eventRepo->createEvent($event);

            $this->addFlash('success', 'Événement créé avec succès !');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/event_form.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'is_edit' => false,
        ]);
    }

    #[Route('/event/{id}/edit', name: 'admin_event_edit')]
    public function editEvent(int $id, Request $request, EventRepository $eventRepo, SluggerInterface $slugger): Response
    {
        $event = $eventRepo->findEventById($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = $this->uploadImage($imageFile, $slugger);
                if ($newFilename) {
                    $event->setImage($newFilename);
                }
            }

            $eventRepo->updateEvent($event);

            $this->addFlash('success', 'Événement mis à jour avec succès !');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/event_form.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'is_edit' => true,
        ]);
    }

    #[Route('/event/{id}/delete', name: 'admin_event_delete', methods: ['POST'])]
    public function deleteEvent(int $id, Request $request, EventRepository $eventRepo): Response
    {
        $event = $eventRepo->findEventById($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        if ($this->isCsrfTokenValid('delete-event-' . $id, $request->request->get('_token'))) {
            $eventRepo->remove($event);
            $this->addFlash('success', 'Événement supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/event/{id}/reservations', name: 'admin_event_reservations')]
    public function eventReservations(int $id, EventRepository $eventRepo, ReservationRepository $resRepo): Response
    {
        $event = $eventRepo->findEventById($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        $reservations = $resRepo->findBy(['event_id' => $event], ['createdat' => 'DESC']);

        return $this->render('admin/reservations.html.twig', [
            'event' => $event,
            'reservations' => $reservations,
        ]);
    }

    private function uploadImage($imageFile, SluggerInterface $slugger): ?string
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move(
                $this->getParameter('kernel.project_dir') . '/public/images/events',
                $newFilename
            );
            return $newFilename;
        } catch (FileException $e) {
            $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
            return null;
        }
    }
}

