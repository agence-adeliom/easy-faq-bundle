<?php

namespace Adeliom\EasyFaqBundle\Routing;

use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class FaqEntryLoader extends Loader
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
        private EntryRepository $repository, /**
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
            throw new \RuntimeException('Do not add the "easy_faq_entry" loader twice');
        }

        $routes = new RouteCollection();

        // prepare a new route
        $hasTrailingSlash = str_ends_with($this->config['root_path'], '/');
        $path = $this->config['root_path'].($hasTrailingSlash?'':'/').'{category}/{entry}'.($hasTrailingSlash?'/':'');
        $defaults = [
            '_controller' => $this->controller.'::index',
        ];
        $requirements = [
            // 'category' => "([a-zA-Z0-9_-]+\/?)*",
            // 'entry' => "([a-zA-Z0-9_-]+\/?)*",
        ];
        $route = new Route($path, $defaults, $requirements, [], '', [], [], "request.attributes.has('_easy_faq_category') && request.attributes.has('_easy_faq_entry')");

        // add the new route to the route collection
        $routeName = 'easy_faq_entry_index';
        $routes->add($routeName, $route, -86);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null): bool
    {
        return 'easy_faq_entry' === $type;
    }
}
