<?php

namespace Adeliom\EasyFaqBundle\DependencyInjection;

use Adeliom\EasyFaqBundle\Controller\CategoryController;
use Adeliom\EasyFaqBundle\Controller\CategoryCrudController;
use Adeliom\EasyFaqBundle\Controller\EntryController;
use Adeliom\EasyFaqBundle\Controller\EntryCrudController;
use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_faq');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('entry')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->isRequired()
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, EntryEntity::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Entry class must be a valid class extending %s. "%s" given.',
                                            EntryEntity::class,
                                            $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('repository')
                            ->defaultValue(EntryRepository::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, EntryRepository::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Entry repository must be a valid class extending %s. "%s" given.',
                                            EntryRepository::class,
                                            $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('controller')
                            ->defaultValue(EntryController::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, EntryController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Page controller must be a valid class extending %s. "%s" given.',
                                            EntryController::class,
                                            $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('crud')
                            ->defaultValue(EntryCrudController::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, EntryCrudController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Entry crud controller must be a valid class extending %s. "%s" given.',
                                            EntryCrudController::class,
                                            $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('category')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->isRequired()
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, CategoryEntity::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Category class must be a valid class extending %s. "%s" given.',
                                            CategoryEntity::class,
                                            $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('repository')
                            ->defaultValue(CategoryRepository::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, CategoryRepository::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Category repository must be a valid class extending %s. "%s" given.',
                                            CategoryRepository::class,
                                            $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('controller')
                            ->defaultValue(CategoryController::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, CategoryController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Category controller must be a valid class extending %s. "%s" given.',
                                            CategoryController::class,
                                            $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('crud')
                            ->defaultValue(CategoryCrudController::class)
                            ->validate()
                                ->ifString()
                                ->then(function ($value) {
                                    if (!class_exists($value) || !is_a($value, CategoryCrudController::class, true)) {
                                        throw new InvalidConfigurationException(sprintf(
                                            'Category crud controller must be a valid class extending %s. "%s" given.',
                                            CategoryCrudController::class,
                                            $value
                                        ));
                                    }
                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->integerNode('ttl')->defaultValue(300)->end()
                    ->end()
                ->end()
                ->arrayNode('page')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('root_path')->defaultValue('/faq')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
