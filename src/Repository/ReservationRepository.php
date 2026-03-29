<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

        public function findAllReservations(): array
    {
        return $this->findAll();
    }

    public function findReservationById(int $id): ?Reservation
    {
        return $this->find($id);
    }

    public function createReservation(Reservation $reservation): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($reservation);
        $entityManager->flush();
    }

    public function updateReservation(Reservation $reservation): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->flush();
    }

    public function remove(Reservation $reservation): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($reservation);
        $entityManager->flush();
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

}
