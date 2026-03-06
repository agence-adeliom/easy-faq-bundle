<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Repository;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyFaqBundle\Repository\EntryRepository::class)]
final class EntryRepositoryTest extends TestCase
{
    public function testGetPublishedQueryConfiguresStateAndPublicationDates(): void
    {
        $builder = $this->createConfiguredBuilder();
        $builder->expects(self::once())->method('where')->with('entry.state = :state')->willReturnSelf();
        $builder->expects(self::exactly(2))->method('andWhere')->willReturnSelf();
        $parameters = [];
        $builder->expects(self::exactly(3))->method('setParameter')
            ->willReturnCallback(function (string $key, mixed $value) use (&$parameters, $builder): QueryBuilder {
                $parameters[] = [$key, $value];

                return $builder;
            });

        $repository = new EntryRepositoryHarness($builder);

        self::assertSame($builder, $repository->getPublishedQuery());
        self::assertSame(['state', ThreeStateStatusEnum::PUBLISHED], $parameters[0]);
        self::assertSame('publishDate', $parameters[1][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[1][1]);
        self::assertSame('unpublishDate', $parameters[2][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[2][1]);
    }

    public function testGetPublishedReturnsBuilderOrResultsDependingOnFlagAndCache(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('enableResultCache')->with(120)->willReturnSelf();
        $query->expects(self::once())->method('getResult')->willReturn(['entry']);

        $builder = $this->createConfiguredBuilder($query);

        $repository = new EntryRepositoryHarness($builder);
        $repository->setConfig(['enabled' => true, 'ttl' => 120]);

        self::assertSame($builder, $repository->getPublished(true));
        self::assertSame(['entry'], $repository->getPublished());
    }

    public function testGetByCategoryReturnsBuilderOrResultsDependingOnFlag(): void
    {
        $category = new CategoryEntity();
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('disableResultCache')->willReturnSelf();
        $query->expects(self::once())->method('getResult')->willReturn(['entry']);

        $builder = $this->createConfiguredBuilder($query);
        $builder->method('andWhere')->willReturnSelf();
        $builder->method('setParameter')->willReturnSelf();

        $repository = new EntryRepositoryHarness($builder);
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);

        self::assertSame($builder, $repository->getByCategory($category, true));
        self::assertSame(['entry'], $repository->getByCategory($category));
    }

    public function testGetBySlugHandlesOptionalCategoryAndSingleResult(): void
    {
        $entry = new EntryEntity();
        $category = new CategoryEntity();

        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('disableResultCache')->willReturnSelf();
        $query->expects(self::once())->method('getOneOrNullResult')->willReturn($entry);

        $builder = $this->createConfiguredBuilder($query);
        $builder->expects(self::once())->method('setMaxResults')->with(1)->willReturnSelf();

        $repository = new EntryRepositoryHarness($builder);
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);

        self::assertSame($entry, $repository->getBySlug('question', $category));
    }

    public function testGetBySlugCanReturnQueryBuilderWhenRequested(): void
    {
        $builder = $this->createConfiguredBuilder();

        $repository = new EntryRepositoryHarness($builder);

        self::assertSame($builder, $repository->getBySlug('question', null, true));
    }

    private function createConfiguredBuilder(?Query $query = null): QueryBuilder
    {
        $builder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'andWhere', 'setParameter', 'getQuery', 'setMaxResults', 'expr'])
            ->getMock();

        $builder->method('where')->willReturnSelf();
        $builder->method('andWhere')->willReturnSelf();
        $builder->method('setParameter')->willReturnSelf();
        $builder->method('getQuery')->willReturn($query ?? $this->createMock(Query::class));
        $builder->method('expr')->willReturn(new Expr());

        return $builder;
    }
}

final class EntryRepositoryHarness extends EntryRepository
{
    public function __construct(private QueryBuilder $builder)
    {
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->builder;
    }
}
