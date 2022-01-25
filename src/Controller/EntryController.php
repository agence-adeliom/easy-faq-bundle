<?php

namespace Adeliom\EasyFaqBundle\Controller;

use Adeliom\EasyFaqBundle\Event\EasyFaqEntryEvent;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Adeliom\EasySeoBundle\Services\BreadcrumbCollection;
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


    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'event_dispatcher' => '?'.EventDispatcherInterface::class,
            'easy_seo.breadcrumb' => '?'.BreadcrumbCollection::class,
        ]);
    }

    public function index(Request $request, string $category = '', string $entry = '', string $_locale = null): Response
    {
        $breadcrumb = $this->get('easy_seo.breadcrumb');

        $this->request = $request;
        $this->request->setLocale($_locale ?: $this->request->getLocale());

        $breadcrumb->addRouteItem('homepage', ['route' => "easy_page_index"]);
        $breadcrumb->addRouteItem('faq', ['route' => "easy_faq_category_index"]);

        $this->categoryRepository = $this->getDoctrine()->getRepository($this->getParameter('easy_faq.category.class'));
        $this->entryRepository = $this->getDoctrine()->getRepository($this->getParameter('easy_faq.entry.class'));

        $template = '@EasyFaq/front/entry.html.twig';

        $categories = $this->categoryRepository->getPublished();

        $category = $request->attributes->get("_easy_faq_category");
        $entry = $request->attributes->get("_easy_faq_entry");

        $breadcrumb->addRouteItem($category->getName(), ['route' => "easy_faq_category_index", 'params' => ['category' => $category->getSlug()]]);
        $breadcrumb->addRouteItem($entry->getName(), ['route' => "easy_faq_entry_index", 'params' => ['category' => $category->getSlug(), 'entry' => $entry->getSlug()]]);

        $args = [
            'categories' => $categories,
            'category' => $category,
            'entry'  => $entry,
            'breadcrumb' => $breadcrumb
        ];
        $event = new EasyFaqEntryEvent($entry, $args, $template);
        /**
         * @var EasyFaqEntryEvent $result;
         */
        $result = $this->get("event_dispatcher")->dispatch($event, EasyFaqEntryEvent::NAME);

        return $this->render($result->getTemplate(), $result->getArgs());
    }

}
