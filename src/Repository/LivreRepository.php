<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    public function findBySearchCriteria(string $searchTerm, string $searchType = 'all'): array
    {
        $qb = $this->getSearchQueryBuilder($searchTerm, $searchType);
        return $qb->getQuery()->getResult();
    }

    public function getSearchQueryBuilder(?string $searchTerm = null, string $searchType = 'all'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l');

        if ($searchTerm) {
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
        }

        return $qb->orderBy('l.id', 'DESC');
    }
}
