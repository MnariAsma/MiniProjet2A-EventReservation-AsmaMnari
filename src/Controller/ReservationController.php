<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }

    #[Route('/event/{id}/reserve', name: 'event_reserve')]
    public function reserve(int $id, Request $request, EventRepository $repo, ReservationRepository $resRepo, MailerInterface $mailer): Response
    {
        $event = $repo->findEventById($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        $currentReservations = $resRepo->count(['event_id' => $event]);
        if ($event->getSeats() && $currentReservations >= $event->getSeats()) {
            $this->addFlash('error', 'Désolé, cet événement est déjà complet.');
            return $this->redirectToRoute('event_details', ['id' => $event->getId()]);
        }

        $reservation = new Reservation();
        $reservation->setEventId($event);

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setCreatedat(new \DateTime());
            
            $entityManager = $resRepo->getEntityManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            // Envoi de l'email de confirmation
            $email = (new TemplatedEmail())
                ->from('no-reply@topevents.com')
                ->to($reservation->getEmail())
                ->subject('Confirmation de réservation : ' . $event->getTitle())
                ->htmlTemplate('email/reservation_confirmation.html.twig')
                ->context([
                    'event' => $event,
                    'reservation' => $reservation,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Votre réservation a été confirmée ! Un e-mail de confirmation a été envoyé.');

            return $this->redirectToRoute('event_details', ['id' => $event->getId()]);
        }

        return $this->render('reservation/reserve.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}
