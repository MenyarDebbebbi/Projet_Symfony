<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    public function findBySearchCriteria(string $searchTerm, string $searchType = 'all'): array
    {
        $qb = $this->createQueryBuilder('l');

        if ($searchType === 'titre') {
            $qb->where('l.titre LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        } elseif ($searchType === 'priorite') {
            $qb->where('l.priorite LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        } else {
            $qb->where('l.titre LIKE :term OR l.priorite LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
