<?php

namespace Adeliom\EasyFaqBundle\Routing;

use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class FaqCategoryLoader extends Loader
{
    private bool $isLoaded = false;

    public function __construct(/**
         * @readonly
         */
        private string $controller, /**
         * @readonly
         */
        private string $entity, /**
         * @readonly
         */
        private CategoryRepository $repository, /**
         * @readonly
         */
        private array $config,
        string $env = null
    ) {
        parent::__construct($env);
    }

    public function load($resource, string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Do not add the "easy_faq_category" loader twice');
        }

        $routes = new RouteCollection();

        // prepare a new route
        $path = $this->config['root_path'].'/{category}';
        $defaults = [
            '_controller' => $this->controller.'::index',
            'category' => '',
        ];
        $requirements = [
            // 'category' => "([a-zA-Z0-9_-]+\/?)*",
        ];
        $route = new Route($path, $defaults, $requirements, [], '', [], [], "request.attributes.has('_easy_faq_category') || request.attributes.get('_easy_faq_root') === true");

        // add the new route to the route collection
        $routeName = 'easy_faq_category_index';
        $routes->add($routeName, $route, -85);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'easy_faq_category' === $type;
    }
}
