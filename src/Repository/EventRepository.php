<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

     public function findAllEvents(): array
    {
        return $this->findAll();
    }

    public function findEventById(int $id): ?Event
    {
        return $this->find($id);
    }

    public function createEvent(Event $event): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($event);
        $entityManager->flush();
    }

    public function updateEvent(Event $event): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->flush();
    }

    public function remove(Event $event): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($event);
        $entityManager->flush();
    }
   
}
