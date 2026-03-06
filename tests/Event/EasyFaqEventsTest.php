<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Event;

use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Adeliom\EasyFaqBundle\Event\EasyFaqCategoryEvent;
use Adeliom\EasyFaqBundle\Event\EasyFaqEntryEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyFaqBundle\Event\EasyFaqCategoryEvent::class)]
#[CoversClass(\Adeliom\EasyFaqBundle\Event\EasyFaqEntryEvent::class)]
final class EasyFaqEventsTest extends TestCase
{
    public function testEventsExposeMutablePayload(): void
    {
        if (!class_exists(\Adeliom\EasyFaqBundle\Entity\BaseEntryEntity::class, false)) {
            class_alias(EntryEntity::class, \Adeliom\EasyFaqBundle\Entity\BaseEntryEntity::class);
        }

        $category = new CategoryEntity();
        $category->setName('General');

        $categoryEvent = new EasyFaqCategoryEvent($category, ['foo' => 'bar'], '@EasyFaq/front/category.html.twig');

        self::assertSame(EasyFaqCategoryEvent::NAME, 'EasyFaq.category.before_render');
        self::assertSame($category, $categoryEvent->getEntry());
        self::assertSame(['foo' => 'bar'], $categoryEvent->getArgs());
        self::assertSame('@EasyFaq/front/category.html.twig', $categoryEvent->getTemplate());

        $categoryEvent->setArgs(['baz' => 'qux']);
        $categoryEvent->setTemplate('@EasyFaq/front/root.html.twig');

        self::assertSame(['baz' => 'qux'], $categoryEvent->getArgs());
        self::assertSame('@EasyFaq/front/root.html.twig', $categoryEvent->getTemplate());

        $entry = new EntryEntity();
        $entry->setName('Question');
        $entryEvent = new EasyFaqEntryEvent($entry, ['entry' => $entry], '@EasyFaq/front/entry.html.twig');

        self::assertSame(EasyFaqEntryEvent::NAME, 'EasyFaq.entry.before_render');
        self::assertSame($entry, $entryEvent->getEntry());
        self::assertSame(['entry' => $entry], $entryEvent->getArgs());
        self::assertSame('@EasyFaq/front/entry.html.twig', $entryEvent->getTemplate());

        $entryEvent->setArgs(['entry' => null]);
        $entryEvent->setTemplate('faq/custom.html.twig');

        self::assertSame(['entry' => null], $entryEvent->getArgs());
        self::assertSame('faq/custom.html.twig', $entryEvent->getTemplate());
    }
}
