<?php

namespace App\Repository;

use App\Entity\Editeur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EditeurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Editeur::class);
    }

    public function findBySearchCriteria(string $searchTerm, string $searchType = 'all'): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($searchType === 'nom') {
            $qb->where('e.nom LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        } elseif ($searchType === 'pays') {
            $qb->where('e.pays LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        } else {
            $qb->where('e.nom LIKE :term OR e.pays LIKE :term OR e.adresse LIKE :term OR e.telephone LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
