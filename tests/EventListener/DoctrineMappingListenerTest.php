<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\EventListener;

use Adeliom\EasyFaqBundle\EventListener\DoctrineMappingListener;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Entity\TestCategory;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Entity\TestEntry;
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
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::exactly(2))
            ->method('getName')
            ->willReturn(TestEntry::class);
        $metadata->expects(self::once())
            ->method('hasAssociation')
            ->with('category')
            ->willReturn(false);
        $metadata->expects(self::once())
            ->method('mapManyToOne')
            ->with([
                'fieldName' => 'category',
                'targetEntity' => TestCategory::class,
                'inversedBy' => 'entries',
            ]);

        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->expects(self::once())
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $listener->loadClassMetadata($event);
    }

    public function testListenerMapsCategoryEntriesAssociation(): void
    {
        $listener = new DoctrineMappingListener(TestEntry::class, TestCategory::class);
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::exactly(2))
            ->method('getName')
            ->willReturn(TestCategory::class);
        $metadata->expects(self::once())
            ->method('hasAssociation')
            ->with('entries')
            ->willReturn(false);
        $metadata->expects(self::once())
            ->method('mapOneToMany')
            ->with([
                'fieldName' => 'entries',
                'targetEntity' => TestEntry::class,
                'mappedBy' => 'category',
            ]);

        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->expects(self::once())
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $listener->loadClassMetadata($event);
    }
}
