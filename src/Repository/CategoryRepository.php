<?php

namespace Adeliom\EasyFaqBundle\Repository;

use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;


class CategoryRepository extends ServiceEntityRepository {

    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var int
     */
    protected $cacheTtl;

    /**
     * @param array $cacheConfig
     */
    public function setConfig(array $cacheConfig)
    {
        $this->cacheEnabled = $cacheConfig['enabled'];
        $this->cacheTtl     = $cacheConfig['ttl'];
    }

    /**
     * @return QueryBuilder
     */
    public function getPublishedQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('category')
            ->where('category.status = :status')
        ;

        $qb->setParameter('status', true);
        return $qb;
    }

    /**
     * @return CategoryEntity[]
     */
    public function getPublished()
    {
        $qb = $this->getPublishedQuery();
        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }

    /**
     * @return CategoryEntity
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBySlug(string $slug)
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere("category.slug = :slug")
            ->setParameter('slug', $slug)
            ->setMaxResults(1);
        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getOneOrNullResult();
    }
}
