<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Controller;

use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Adeliom\EasyFaqBundle\Controller\EntryController;
use Adeliom\EasySeoBundle\Services\BreadcrumbCollection;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(\Adeliom\EasyFaqBundle\Controller\EntryController::class)]
final class EntryControllerTest extends TestCase
{
    public function testSubscribedServicesExposeDispatcherAndBreadcrumb(): void
    {
        $services = EntryControllerDouble::getSubscribedServices();

        self::assertSame('?'.EventDispatcherInterface::class, $services['event_dispatcher']);
        self::assertSame('?'.BreadcrumbCollection::class, $services['easy_seo.breadcrumb']);
    }

    public function testIndexRendersEntryPage(): void
    {
        $category = new CategoryEntity();
        $category->setName('General');
        $category->setSlug('general');

        $entry = new EntryEntity();
        $entry->setName('Question');
        $entry->setSlug('question');
        $entry->setCategory($category);

        $request = Request::create('/faq/general/question');
        $request->attributes->set('_easy_faq_category', $category);
        $request->attributes->set('_easy_faq_entry', $entry);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects(self::once())->method('getPublished')->willReturn([$category]);

        $entryRepository = $this->createMock(EntryRepository::class);
        $entryRepository->expects(self::never())->method('getBySlug');

        $controller = new EntryControllerDouble($this->createManagerRegistry($categoryRepository, $entryRepository));
        $controller->setParameters([
            'easy_faq.category.class' => CategoryEntity::class,
            'easy_faq.entry.class' => EntryEntity::class,
        ]);
        $controller->setContainer($this->createContainer(
            $this->createBreadcrumb(),
            $this->createEventDispatcher()
        ));

        $response = $controller->index($request, 'general', 'question', 'fr');

        self::assertSame('ok', $response->getContent());
        self::assertSame('@EasyFaq/front/entry.html.twig', $controller->lastTemplate);
        self::assertSame($entry, $controller->lastParameters['entry']);
        self::assertSame($category, $controller->lastParameters['category']);
        self::assertSame('fr', $request->getLocale());
    }

    private function createManagerRegistry(CategoryRepository $categoryRepository, EntryRepository $entryRepository): ManagerRegistry
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->method('getRepository')
            ->willReturnMap([
                [CategoryEntity::class, null, $categoryRepository],
                [EntryEntity::class, null, $entryRepository],
            ]);

        return $registry;
    }

    private function createBreadcrumb(): BreadcrumbCollection
    {
        $generator = new class implements \Symfony\Component\Routing\Generator\UrlGeneratorInterface {
            private \Symfony\Component\Routing\RequestContext $context;

            public function __construct()
            {
                $this->context = new \Symfony\Component\Routing\RequestContext();
            }

            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return '/'.$name.([] === $parameters ? '' : '?'.http_build_query($parameters));
            }

            public function setContext(\Symfony\Component\Routing\RequestContext $context): void
            {
                $this->context = $context;
            }

            public function getContext(): \Symfony\Component\Routing\RequestContext
            {
                return $this->context;
            }
        };

        $breadcrumb = new BreadcrumbCollection();
        $breadcrumb->setGenerator($generator);

        return $breadcrumb;
    }

    private function createEventDispatcher(): EventDispatcherInterface
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnArgument(0);

        return $dispatcher;
    }

    private function createContainer(BreadcrumbCollection $breadcrumb, EventDispatcherInterface $dispatcher): ContainerInterface
    {
        return new class($breadcrumb, $dispatcher) implements ContainerInterface {
            public function __construct(private BreadcrumbCollection $breadcrumb, private EventDispatcherInterface $dispatcher)
            {
            }

            public function get(string $id)
            {
                return match ($id) {
                    'easy_seo.breadcrumb' => $this->breadcrumb,
                    'event_dispatcher' => $this->dispatcher,
                    default => throw new \InvalidArgumentException('Unknown service '.$id),
                };
            }

            public function has(string $id): bool
            {
                return \in_array($id, ['easy_seo.breadcrumb', 'event_dispatcher'], true);
            }
        };
    }
}

final class EntryControllerDouble extends EntryController
{
    public string $lastTemplate = '';

    public array $lastParameters = [];

    private array $parameters = [];

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    protected function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
    {
        return $this->parameters[$name];
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->lastTemplate = $view;
        $this->lastParameters = $parameters;

        return new Response('ok');
    }
}
