<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\EventListener;

use Adeliom\EasyFaqBundle\EventListener\DoctrineMappingListener;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Entity\TestCategory;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Entity\TestEntry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyFaqBundle\EventListener\DoctrineMappingListener::class)]
final class DoctrineMappingListenerTest extends TestCase
{
    public function testListenerMapsEntryCategoryAssociation(): void
    {
        $listener = new DoctrineMappingListener(TestEntry::class, TestCategory::class);
        $metadata = new ClassMetadata(TestEntry::class);
        $event = new LoadClassMetadataEventArgs($metadata, $this->createMock(EntityManagerInterface::class));

        $listener->loadClassMetadata($event);

        $mapping = $metadata->getAssociationMapping('category');

        self::assertSame('category', $mapping->fieldName);
        self::assertSame(TestCategory::class, $mapping->targetEntity);
        self::assertSame('entries', $mapping->inversedBy);
    }

    public function testListenerMapsCategoryEntriesAssociation(): void
    {
        $listener = new DoctrineMappingListener(TestEntry::class, TestCategory::class);
        $metadata = new ClassMetadata(TestCategory::class);
        $event = new LoadClassMetadataEventArgs($metadata, $this->createMock(EntityManagerInterface::class));

        $listener->loadClassMetadata($event);

        $mapping = $metadata->getAssociationMapping('entries');

        self::assertSame('entries', $mapping->fieldName);
        self::assertSame(TestEntry::class, $mapping->targetEntity);
        self::assertSame('category', $mapping->mappedBy);
    }
}
