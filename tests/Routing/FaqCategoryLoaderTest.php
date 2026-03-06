<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Routing;

use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Routing\FaqCategoryLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(\Adeliom\EasyFaqBundle\Routing\FaqCategoryLoader::class)]
final class FaqCategoryLoaderTest extends TestCase
{
    public function testLoaderBuildsCategoryRoute(): void
    {
        $loader = new FaqCategoryLoader(
            'App\\Controller\\CategoryController',
            'App\\Entity\\Category',
            $this->createMock(CategoryRepository::class),
            ['root_path' => '/faq']
        );

        self::assertTrue($loader->supports(null, 'easy_faq_category'));

        $routes = $loader->load(null, 'easy_faq_category');

        self::assertInstanceOf(RouteCollection::class, $routes);
        self::assertSame('/faq/{category}', $routes->get('easy_faq_category_index')->getPath());
        self::assertSame('App\\Controller\\CategoryController::index', $routes->get('easy_faq_category_index')->getDefault('_controller'));
        self::assertSame("request.attributes.has('_easy_faq_category') || request.attributes.get('_easy_faq_root') === true", $routes->get('easy_faq_category_index')->getCondition());
    }

    public function testLoaderCannotBeLoadedTwice(): void
    {
        $loader = new FaqCategoryLoader(
            'App\\Controller\\CategoryController',
            'App\\Entity\\Category',
            $this->createMock(CategoryRepository::class),
            ['root_path' => '/faq/']
        );

        $loader->load(null, 'easy_faq_category');

        $this->expectException(\RuntimeException::class);

        $loader->load(null, 'easy_faq_category');
    }
}
