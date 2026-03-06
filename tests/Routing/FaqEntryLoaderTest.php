<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Routing;

use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Adeliom\EasyFaqBundle\Routing\FaqEntryLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(\Adeliom\EasyFaqBundle\Routing\FaqEntryLoader::class)]
final class FaqEntryLoaderTest extends TestCase
{
    public function testLoaderBuildsEntryRouteWithTrailingSlash(): void
    {
        $loader = new FaqEntryLoader(
            'App\\Controller\\EntryController',
            'App\\Entity\\Entry',
            $this->createMock(EntryRepository::class),
            ['root_path' => '/faq/']
        );

        self::assertTrue($loader->supports(null, 'easy_faq_entry'));

        $routes = $loader->load(null, 'easy_faq_entry');

        self::assertInstanceOf(RouteCollection::class, $routes);
        self::assertSame('/faq/{category}/{entry}/', $routes->get('easy_faq_entry_index')->getPath());
        self::assertSame('App\\Controller\\EntryController::index', $routes->get('easy_faq_entry_index')->getDefault('_controller'));
        self::assertSame("request.attributes.has('_easy_faq_category') && request.attributes.has('_easy_faq_entry')", $routes->get('easy_faq_entry_index')->getCondition());
    }

    public function testLoaderCannotBeLoadedTwice(): void
    {
        $loader = new FaqEntryLoader(
            'App\\Controller\\EntryController',
            'App\\Entity\\Entry',
            $this->createMock(EntryRepository::class),
            ['root_path' => '/faq']
        );

        $loader->load(null, 'easy_faq_entry');

        $this->expectException(\RuntimeException::class);

        $loader->load(null, 'easy_faq_entry');
    }
}
