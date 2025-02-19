<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function getAll(): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            // on selectionne les tables team et session, avec un join de session sur t.session
            'SELECT a, u
            FROM App\Entity\Article a
            INNER JOIN a.author u
            ORDER BY a.id DESC'
        );
        return $query->getResult();
    }
}
