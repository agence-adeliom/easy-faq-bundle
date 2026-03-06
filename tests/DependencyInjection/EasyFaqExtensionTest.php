<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\DependencyInjection;

use Adeliom\EasyFaqBundle\DependencyInjection\EasyFaqExtension;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Admin\TestCategoryCrudController;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Admin\TestEntryCrudController;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Category\TestCategoryController;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Entry\TestEntryController;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Entity\TestCategory;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Entity\TestEntry;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Repository\TestCategoryRepository;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Repository\TestEntryRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(\Adeliom\EasyFaqBundle\DependencyInjection\EasyFaqExtension::class)]
final class EasyFaqExtensionTest extends TestCase
{
    public function testExtensionLoadsParametersAndServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new EasyFaqExtension();

        $extension->load([[
            'entry' => [
                'class' => TestEntry::class,
                'repository' => TestEntryRepository::class,
                'controller' => TestEntryController::class,
                'crud' => TestEntryCrudController::class,
            ],
            'category' => [
                'class' => TestCategory::class,
                'repository' => TestCategoryRepository::class,
                'controller' => TestCategoryController::class,
                'crud' => TestCategoryCrudController::class,
            ],
            'cache' => [
                'enabled' => true,
                'ttl' => 1200,
            ],
            'page' => [
                'root_path' => '/help',
            ],
            'sitemap' => false,
        ]], $container);

        self::assertSame('easy_faq', $extension->getAlias());
        self::assertSame(TestEntry::class, $container->getParameter('easy_faq.entry.class'));
        self::assertSame(TestEntryRepository::class, $container->getParameter('easy_faq.entry.repository'));
        self::assertSame(TestEntryController::class, $container->getParameter('easy_faq.entry.controller'));
        self::assertSame(TestEntryCrudController::class, $container->getParameter('easy_faq.entry.crud'));
        self::assertSame(TestCategory::class, $container->getParameter('easy_faq.category.class'));
        self::assertSame(TestCategoryRepository::class, $container->getParameter('easy_faq.category.repository'));
        self::assertSame(TestCategoryController::class, $container->getParameter('easy_faq.category.controller'));
        self::assertSame(TestCategoryCrudController::class, $container->getParameter('easy_faq.category.crud'));
        self::assertSame(['enabled' => true, 'ttl' => 1200], $container->getParameter('easy_faq.cache'));
        self::assertSame(['root_path' => '/help'], $container->getParameter('easy_faq.page'));
        self::assertFalse($container->getParameter('easy_faq.sitemap'));
        self::assertTrue($container->hasDefinition('easy_faq.entry.route_loader'));
        self::assertTrue($container->hasDefinition('easy_faq.category.route_loader'));
        self::assertTrue($container->hasDefinition('easy_faq.entry.repository'));
        self::assertTrue($container->hasDefinition('easy_faq.category.repository'));
        self::assertTrue($container->hasDefinition('easy_faq.sitemap.subscriber'));
    }
}
