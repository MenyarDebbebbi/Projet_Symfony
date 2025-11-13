<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    public function findBySearchCriteria(string $searchTerm, string $searchType = 'all'): array
    {
        $qb = $this->getSearchQueryBuilder($searchTerm, $searchType);
        return $qb->getQuery()->getResult();
    }

    public function getSearchQueryBuilder(?string $searchTerm = null, string $searchType = 'all'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if ($searchTerm) {
            $qb->where('c.designation LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        }

        return $qb->orderBy('c.id', 'DESC');
    }
}
