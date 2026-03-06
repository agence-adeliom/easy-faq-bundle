<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Entity;

use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyFaqBundle\Entity\CategoryEntity::class)]
final class CategoryEntityTest extends TestCase
{
    public function testCategoryMaintainsEntriesAndPresentationProperties(): void
    {
        $category = new CategoryEntity();
        $category->setName('General');
        $category->setSlug('general');
        $category->setCss('body { color: red; }');
        $category->setJs('console.log("faq");');

        $entry = new EntryEntity();
        $entry->setName('Question');
        $entry->setSlug('question');

        $category->addEntry($entry);

        self::assertCount(1, $category->getEntries());
        self::assertSame($category, $entry->getCategory());
        self::assertSame('body { color: red; }', $category->getCss());
        self::assertSame('console.log("faq");', $category->getJs());

        $category->removeEntry($entry);

        self::assertCount(0, $category->getEntries());
        self::assertNull($entry->getCategory());
    }

    public function testCategoryLifecycleUpdatesSeoAndRemovalState(): void
    {
        $category = new CategoryEntity();
        $category->setName('General');
        $category->setSlug('general');
        $category->getSEO()->title = '';

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $category->setSeoTitle(new PrePersistEventArgs($category, $entityManager));

        self::assertSame('General', $category->getSEO()->title);

        $id = new \ReflectionProperty($category, 'id');
        $id->setValue($category, 4);

        $category->onRemove(new PreRemoveEventArgs($category, $entityManager));

        self::assertFalse($category->getStatus());
        self::assertSame('General-4-deleted', $category->getName());
        self::assertSame('general-4-deleted', $category->getSlug());
    }
}
