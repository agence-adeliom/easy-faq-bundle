<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\DependencyInjection;

use Adeliom\EasyFaqBundle\Controller\CategoryController;
use Adeliom\EasyFaqBundle\Controller\EntryController;
use Adeliom\EasyFaqBundle\DependencyInjection\Configuration;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Category\TestCategoryController;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Entry\TestEntryController;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Admin\TestCategoryCrudController;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Admin\TestEntryCrudController;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Entity\TestCategory;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Entity\TestEntry;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Repository\TestCategoryRepository;
use Adeliom\EasyFaqBundle\Tests\Fixtures\Repository\TestEntryRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

#[CoversClass(\Adeliom\EasyFaqBundle\DependencyInjection\Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function testConfigurationBuildsExpectedRootNode(): void
    {
        $builder = (new Configuration())->getConfigTreeBuilder();

        self::assertSame('easy_faq', $builder->buildTree()->getName());
    }

    public function testConfigurationAcceptsEntitiesAndAppliesDefaults(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'entry' => ['class' => TestEntry::class],
            'category' => ['class' => TestCategory::class],
        ]]);

        self::assertSame(TestEntry::class, $config['entry']['class']);
        self::assertSame(EntryRepository::class, $config['entry']['repository']);
        self::assertSame(EntryController::class, $config['entry']['controller']);
        self::assertSame(\Adeliom\EasyFaqBundle\Controller\EntryCrudController::class, $config['entry']['crud']);
        self::assertSame(TestCategory::class, $config['category']['class']);
        self::assertSame(CategoryRepository::class, $config['category']['repository']);
        self::assertSame(CategoryController::class, $config['category']['controller']);
        self::assertSame(\Adeliom\EasyFaqBundle\Controller\CategoryCrudController::class, $config['category']['crud']);
        self::assertFalse($config['cache']['enabled']);
        self::assertSame(300, $config['cache']['ttl']);
        self::assertSame('/faq', $config['page']['root_path']);
        self::assertTrue($config['sitemap']);
    }

    public function testConfigurationAcceptsCustomValues(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
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
                'ttl' => 900,
            ],
            'page' => [
                'root_path' => '/help',
            ],
            'sitemap' => false,
        ]]);

        self::assertSame(TestEntryRepository::class, $config['entry']['repository']);
        self::assertSame(TestEntryController::class, $config['entry']['controller']);
        self::assertSame(TestEntryCrudController::class, $config['entry']['crud']);
        self::assertSame(TestCategoryRepository::class, $config['category']['repository']);
        self::assertSame(TestCategoryController::class, $config['category']['controller']);
        self::assertSame(TestCategoryCrudController::class, $config['category']['crud']);
        self::assertTrue($config['cache']['enabled']);
        self::assertSame(900, $config['cache']['ttl']);
        self::assertSame('/help', $config['page']['root_path']);
        self::assertFalse($config['sitemap']);
    }

    public function testConfigurationRejectsInvalidEntryClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'entry' => ['class' => \stdClass::class],
            'category' => ['class' => TestCategory::class],
        ]]);
    }

    public function testConfigurationRejectsInvalidCategoryClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'entry' => ['class' => TestEntry::class],
            'category' => ['class' => \stdClass::class],
        ]]);
    }
}
