<?php

namespace Adeliom\EasyFaqBundle\Controller;

use Adeliom\EasyFaqBundle\Event\EasyFaqCategoryEvent;
use Adeliom\EasyFaqBundle\Event\EasyFaqEntryEvent;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Adeliom\EasySeoBundle\Services\BreadCrumbCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntryController extends AbstractController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var EntryRepository
     */
    protected $entryRepository;

    /**
     * @var EntryRepository
     */
    protected $eventDispatcher;

    /**
     * @var BreadCrumbCollection
     */
    protected $breadcrumb;

    public function setRepositories(CategoryRepository $categoryRepository, EntryRepository $entryRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entryRepository = $entryRepository;
    }

    public function index(Request $request, string $category = '', string $entry = '', string $_locale = null, EventDispatcherInterface $eventDispatcher, BreadCrumbCollection $breadcrumb): Response
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->breadcrumb = $breadcrumb;

        $this->request = $request;
        $this->request->setLocale($_locale ?: $this->request->getLocale());

        $this->breadcrumb->addRouteItem('homepage', ['route' => "easy_page_index"]);
        $this->breadcrumb->addRouteItem('faq', ['route' => "easy_faq_category_index"]);


        $template = '@EasyFaq/front/entry.html.twig';

        $categories = $this->categoryRepository->getPublished();
        $category = $this->categoryRepository->getBySlug($category);
        $entry = $this->entryRepository->getBySlug($entry, $category);

        $this->breadcrumb->addRouteItem($category->getName(), ['route' => "easy_faq_category_index", 'params' => ['category' => $category->getSlug()]]);
        $this->breadcrumb->addRouteItem($entry->getName(), ['route' => "easy_faq_entry_index", 'params' => ['category' => $category->getSlug(), 'entry' => $entry->getSlug()]]);

        $args = [
            'categories' => $categories,
            'category' => $category,
            'entry'  => $entry,
            'breadcrumb' => $breadcrumb
        ];
        $event = new EasyFaqEntryEvent($entry, $args, $template);
        /**
         * @var EasyFaqCategoryEvent $result;
         */
        $result = $this->eventDispatcher->dispatch($event, EasyFaqCategoryEvent::NAME);

        return $this->render($result->getTemplate(), $result->getArgs());
    }

}
