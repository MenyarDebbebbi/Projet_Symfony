<?php

namespace App\Repository;

use App\Entity\Auteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AuteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auteur::class);
    }

    public function findBySearchCriteria(string $searchTerm, string $searchType = 'all'): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($searchType === 'nom') {
            $qb->where('a.nom LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        } elseif ($searchType === 'prenom') {
            $qb->where('a.prenom LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        } else {
            $qb->where('a.nom LIKE :term OR a.prenom LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
