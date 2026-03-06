<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\EventListener;

use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Adeliom\EasyFaqBundle\EventListener\EntryListener;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(\Adeliom\EasyFaqBundle\EventListener\EntryListener::class)]
final class EntryListenerTest extends TestCase
{
    public function testSubscribedEventsExposeRequestListener(): void
    {
        self::assertSame([
            KernelEvents::REQUEST => ['setRequestLayout', 33],
        ], EntryListener::getSubscribedEvents());
    }

    public function testListenerMarksRootPathRequest(): void
    {
        $entryRepository = $this->createMock(EntryRepository::class);
        $entryRepository->expects(self::never())->method('getBySlug');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::never())->method('getBySlug');

        $listener = new EntryListener($entryRepository, $categoryRepository, ['root_path' => '/faq']);
        $request = Request::create('/faq');

        $listener->setRequestLayout($this->createRequestEvent($request));

        self::assertTrue($request->attributes->getBoolean('_easy_faq_root'));
    }

    public function testListenerLoadsCategoryAndEntryFromRequestPath(): void
    {
        $category = new CategoryEntity();
        $category->setName('General');
        $category->setSlug('general');

        $entry = new EntryEntity();
        $entry->setName('Question');
        $entry->setSlug('question');
        $entry->setCategory($category);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::once())
            ->method('getBySlug')
            ->with('general')
            ->willReturn($category);

        $entryRepository = $this->createMock(EntryRepository::class);
        $entryRepository->expects(self::once())
            ->method('getBySlug')
            ->with('question', $category)
            ->willReturn($entry);

        $listener = new EntryListener($entryRepository, $categoryRepository, ['root_path' => '/faq']);
        $request = Request::create('/faq/general/question');

        $listener->setRequestLayout($this->createRequestEvent($request));

        self::assertSame($category, $request->attributes->get('_easy_faq_category'));
        self::assertSame($entry, $request->attributes->get('_easy_faq_entry'));
    }

    public function testListenerIgnoresPathsOutsideConfiguredRoot(): void
    {
        $entryRepository = $this->createMock(EntryRepository::class);
        $entryRepository->expects(self::never())->method('getBySlug');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::never())->method('getBySlug');

        $listener = new EntryListener($entryRepository, $categoryRepository, ['root_path' => '/faq']);
        $request = Request::create('/contact');

        $listener->setRequestLayout($this->createRequestEvent($request));

        self::assertFalse($request->attributes->has('_easy_faq_root'));
        self::assertFalse($request->attributes->has('_easy_faq_category'));
        self::assertFalse($request->attributes->has('_easy_faq_entry'));
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
