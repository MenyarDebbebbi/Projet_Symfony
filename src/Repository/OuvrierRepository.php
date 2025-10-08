<?php

namespace App\Repository;

use App\Entity\Ouvrier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ouvrier>
 */
class OuvrierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ouvrier::class);
    }

    public function findBySearchCriteria(?string $searchTerm = null, ?string $searchType = 'all'): array
    {
        $qb = $this->createQueryBuilder('o');

        if ($searchTerm) {
            switch ($searchType) {
                case 'nom':
                    $qb->andWhere('o.nom LIKE :searchTerm')
                        ->setParameter('searchTerm', '%' . $searchTerm . '%');
                    break;
                case 'grade':
                    $qb->andWhere('o.grade LIKE :searchTerm')
                        ->setParameter('searchTerm', '%' . $searchTerm . '%');
                    break;
                case 'all':
                default:
                    $qb->andWhere('o.nom LIKE :searchTerm OR o.prenom LIKE :searchTerm OR o.grade LIKE :searchTerm')
                        ->setParameter('searchTerm', '%' . $searchTerm . '%');
                    break;
            }
        }

        return $qb->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
