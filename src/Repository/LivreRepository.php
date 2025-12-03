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

    /**
     * Retourne tous les livres avec leurs relations chargées en jointure,
     * ce qui évite les erreurs "Entity of type X with id Y was not found"
     * sur des associations orphelines.
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.auteur', 'a')->addSelect('a')
            ->leftJoin('l.categorie', 'c')->addSelect('c')
            ->leftJoin('l.editeur', 'e')->addSelect('e')
            ->leftJoin('l.bibliotheque', 'b')->addSelect('b')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les livres filtrés par bibliothèque et/ou catégorie
     */
    public function findFiltered(?int $bibliothequeId = null, ?int $categorieId = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.auteur', 'a')->addSelect('a')
            ->leftJoin('l.categorie', 'c')->addSelect('c')
            ->leftJoin('l.editeur', 'e')->addSelect('e')
            ->leftJoin('l.bibliotheque', 'b')->addSelect('b');

        if ($bibliothequeId !== null) {
            $qb->andWhere('l.bibliotheque = :bibliothequeId')
                ->setParameter('bibliothequeId', $bibliothequeId);
        }

        if ($categorieId !== null) {
            $qb->andWhere('l.categorie = :categorieId')
                ->setParameter('categorieId', $categorieId);
        }

        return $qb->orderBy('l.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne un livre par son ID avec ses relations chargées en jointure,
     * ce qui évite les erreurs "Entity of type X with id Y was not found"
     * sur des associations orphelines.
     */
    public function findOneWithRelations(int $id): ?Livre
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.auteur', 'a')->addSelect('a')
            ->leftJoin('l.categorie', 'c')->addSelect('c')
            ->leftJoin('l.editeur', 'e')->addSelect('e')
            ->leftJoin('l.bibliotheque', 'b')->addSelect('b')
            ->where('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
