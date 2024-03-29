<?php

namespace Adeliom\EasyFaqBundle\Repository;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

class EntryRepository extends ServiceEntityRepository
{
    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var int
     */
    protected $cacheTtl;

    public function setConfig(array $cacheConfig)
    {
        $this->cacheEnabled = $cacheConfig['enabled'];
        $this->cacheTtl = $cacheConfig['ttl'];
    }

    public function getPublishedQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('entry')
            ->where('entry.state = :state')
            ->andWhere('entry.publishDate < :publishDate')
        ;

        $orModule = $qb->expr()->orx();
        $orModule->add($qb->expr()->gt('entry.unpublishDate', ':unpublishDate'));
        $orModule->add($qb->expr()->isNull('entry.unpublishDate'));

        $qb->andWhere($orModule);

        $qb->setParameter('state', ThreeStateStatusEnum::PUBLISHED());
        $qb->setParameter('publishDate', new \DateTime());
        $qb->setParameter('unpublishDate', new \DateTime());

        return $qb;
    }

    /**
     * @return BaseEntryEntity[]
     */
    public function getPublished(bool $returnQueryBuilder = false)
    {
        $qb = $this->getPublishedQuery();
        if ($returnQueryBuilder) {
            return $qb;
        }

        if ($this->cacheEnabled) {
            $qb = $qb->getQuery()->enableResultCache($this->cacheTtl);
        } else {
            $qb = $qb->getQuery()->disableResultCache();
        }

        return $qb->getResult();
    }

    /**
     * @return EntryEntity[]
     */
    public function getByCategory(CategoryEntity $categoryEntity, bool $returnQueryBuilder = false)
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere('entry.category = :category')
            ->setParameter('category', $categoryEntity)
        ;
        if ($returnQueryBuilder) {
            return $qb;
        }

        if ($this->cacheEnabled) {
            $qb = $qb->getQuery()->enableResultCache($this->cacheTtl);
        } else {
            $qb = $qb->getQuery()->disableResultCache();
        }

        return $qb->getResult();
    }

    /**
     * @return EntryEntity
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBySlug(string $slug, ?CategoryEntity $categoryEntity, bool $returnQueryBuilder = false)
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere('entry.slug = :slug')
            ->setParameter('slug', $slug);
        if (null !== $categoryEntity) {
            $qb->andWhere('entry.category = :category')
                ->setParameter('category', $categoryEntity);
        }

        $qb->setMaxResults(1);
        if ($returnQueryBuilder) {
            return $qb;
        }

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getOneOrNullResult();
    }
}
