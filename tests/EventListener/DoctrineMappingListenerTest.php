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
    public function testListenerMapsFaqAssociations(): void
    {
        $listener = new DoctrineMappingListener(TestEntry::class, TestCategory::class);

        $entryMetadata = new ClassMetadata(TestEntry::class);
        $entryEvent = $this->createMock(LoadClassMetadataEventArgs::class);
        $entryEvent->method('getClassMetadata')->willReturn($entryMetadata);

        $listener->loadClassMetadata($entryEvent);

        self::assertTrue($entryMetadata->hasAssociation('category'));

        $categoryMetadata = new ClassMetadata(TestCategory::class);
        $categoryEvent = $this->createMock(LoadClassMetadataEventArgs::class);
        $categoryEvent->method('getClassMetadata')->willReturn($categoryMetadata);

        $listener->loadClassMetadata($categoryEvent);

        self::assertTrue($categoryMetadata->hasAssociation('entries'));
    }
}
