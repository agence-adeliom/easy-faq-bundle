<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\EventListener;

use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Adeliom\EasyFaqBundle\EventListener\SitemapSubscriber;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(\Adeliom\EasyFaqBundle\EventListener\SitemapSubscriber::class)]
final class SitemapSubscriberTest extends TestCase
{
    public function testSubscriberRegistersPublishedCategoriesAndEntries(): void
    {
        $updatedAt = new \DateTimeImmutable('2024-01-01 12:00:00');

        $category = new CategoryEntity();
        $category->setName('General');
        $category->setSlug('general');
        $category->getSEO()->sitemap = true;
        $category->setUpdatedAt(\DateTime::createFromImmutable($updatedAt));

        $entry = new EntryEntity();
        $entry->setName('Question');
        $entry->setSlug('question');
        $entry->setCategory($category);
        $entry->getSEO()->sitemap = true;
        $entry->setUpdatedAt(\DateTime::createFromImmutable($updatedAt));

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->method('getPublished')->willReturn([$category]);

        $entryRepository = $this->createMock(EntryRepository::class);
        $entryRepository->method('getPublished')->willReturn([$entry]);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $generatedRoutes = [];
        $urlGenerator->expects(self::exactly(2))
            ->method('generate')
            ->willReturnCallback(static function (string $name, array $parameters, int $referenceType) use (&$generatedRoutes): string {
                $generatedRoutes[] = [$name, $parameters, $referenceType];

                return match ($name) {
                    'easy_faq_category_index' => 'https://example.com/faq/general',
                    'easy_faq_entry_index' => 'https://example.com/faq/general/question',
                    default => throw new \LogicException(sprintf('Unexpected route "%s".', $name)),
                };
            });

        $urls = $this->createMock(UrlContainerInterface::class);
        $addedUrls = [];
        $urls->expects(self::exactly(2))
            ->method('addUrl')
            ->willReturnCallback(static function ($url, string $section) use (&$addedUrls): void {
                $addedUrls[] = [$url->getLoc(), $section];
            });

        $event = $this->createMock(SitemapPopulateEvent::class);
        $event->method('getUrlContainer')->willReturn($urls);

        $subscriber = new SitemapSubscriber($urlGenerator, $entryRepository, $categoryRepository, true);

        self::assertSame([SitemapPopulateEvent::class => 'populate'], SitemapSubscriber::getSubscribedEvents());

        $subscriber->populate($event);

        self::assertSame([
            ['easy_faq_category_index', ['category' => 'general'], UrlGeneratorInterface::ABSOLUTE_URL],
            ['easy_faq_entry_index', ['category' => 'general', 'entry' => 'question'], UrlGeneratorInterface::ABSOLUTE_URL],
        ], $generatedRoutes);
        self::assertSame([
            ['https://example.com/faq/general', 'faq'],
            ['https://example.com/faq/general/question', 'faq'],
        ], $addedUrls);
    }

    public function testSubscriberSkipsRegistrationWhenDisabled(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::never())->method('getPublished');

        $entryRepository = $this->createMock(EntryRepository::class);
        $entryRepository->expects(self::never())->method('getPublished');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $event = $this->createMock(SitemapPopulateEvent::class);

        (new SitemapSubscriber($urlGenerator, $entryRepository, $categoryRepository, false))->populate($event);

        self::assertTrue(true);
    }
}
