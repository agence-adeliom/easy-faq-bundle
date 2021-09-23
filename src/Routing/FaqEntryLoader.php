<?php

namespace Adeliom\EasyFaqBundle\Routing;


use Adeliom\EasyFaqBundle\Repository\PageRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FaqEntryLoader extends Loader
{
    private $isLoaded = false;

    private $controller;
    private $entity;
    private $repository;
    private $config;


    public function __construct(string $controller, string $entity, EntryRepository $repository, array $config, string $env = null)
    {
        parent::__construct($env);

        $this->controller = $controller;
        $this->config = $config;
        $this->entity = $entity;
        $this->repository = $repository;
    }

    public function load($resource, string $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "easy_faq_entry" loader twice');
        }

        $routes = new RouteCollection();

        // prepare a new route
        $path = $this->config['root_path'] . '/{category}/{entry}';
        $defaults = [
            '_controller' => $this->controller . '::index',
        ];
        $requirements = [
            'category' => "([a-zA-Z0-9_-]+\/?)*",
            'entry' => "([a-zA-Z0-9_-]+\/?)*",
        ];
        $route = new Route($path, $defaults, $requirements, [], '', [], [], "request.attributes.has('_easy_faq_category') && request.attributes.has('_easy_faq_entry')");

        // add the new route to the route collection
        $routeName = 'easy_faq_entry_index';
        $routes->add($routeName, $route, -80);

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null)
    {
        return 'easy_faq_entry' === $type;
    }
}
