<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyFaqBundle\Entity\EntryEntity::class)]
final class EntryEntityTest extends TestCase
{
    public function testEntryMaintainsCategoryAndMetadata(): void
    {
        $category = new CategoryEntity();
        $category->setName('General');
        $category->setSlug('general');

        $entry = new EntryEntity();
        $entry->setName('Question');
        $entry->setSlug('question');
        $entry->setCategory($category);
        $entry->setAnswer(['type' => 'paragraph', 'content' => 'Answer']);
        $entry->setCss('body { color: blue; }');
        $entry->setJs('console.log("entry");');

        self::assertSame($category, $entry->getCategory());
        self::assertSame(['type' => 'paragraph', 'content' => 'Answer'], $entry->getAnswer());
        self::assertSame('body { color: blue; }', $entry->getCss());
        self::assertSame('console.log("entry");', $entry->getJs());
    }

    public function testEntryLifecycleUpdatesSeoAndRemovalState(): void
    {
        $entry = new EntryEntity();
        $entry->setName('Question');
        $entry->setSlug('question');
        $entry->getSEO()->title = '';

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entry->setSeoTitle(new PrePersistEventArgs($entry, $entityManager));

        self::assertSame('Question', $entry->getSEO()->title);

        $id = new \ReflectionProperty($entry, 'id');
        $id->setValue($entry, 10);

        $entry->onRemove(new PreRemoveEventArgs($entry, $entityManager));

        self::assertSame(ThreeStateStatusEnum::UNPUBLISHED, $entry->getState());
        self::assertSame('Question-10-deleted', $entry->getName());
        self::assertSame('question-10-deleted', $entry->getSlug());
    }
}
