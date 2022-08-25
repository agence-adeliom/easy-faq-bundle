<?php

namespace Adeliom\EasyFaqBundle\Controller;

use Adeliom\EasyFaqBundle\Event\EasyFaqCategoryEvent;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Adeliom\EasySeoBundle\Entity\SEO;
use Adeliom\EasySeoBundle\Services\BreadcrumbCollection;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CategoryController extends AbstractController
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


    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'event_dispatcher' => '?' . EventDispatcherInterface::class,
            'easy_seo.breadcrumb' => '?' . BreadcrumbCollection::class,
        ]);
    }

    public function index(Request $request, string $category = '', string $_locale = null): Response
    {
        $breadcrumb = $this->container->get('easy_seo.breadcrumb');
        $this->request = $request;
        $this->request->setLocale($_locale ?: $this->request->getLocale());

        $this->categoryRepository = $this->managerRegistry->getRepository($this->getParameter('easy_faq.category.class'));
        $this->entryRepository = $this->managerRegistry->getRepository($this->getParameter('easy_faq.entry.class'));

        $breadcrumb->addRouteItem('homepage', ['route' => "easy_page_index"]);
        $breadcrumb->addRouteItem('faq', ['route' => "easy_faq_category_index"]);

        if ($this->request->attributes->get("_easy_faq_root")) {
            return $this->faqRoot();
        }

        $template = '@EasyFaq/front/category.html.twig';

        $category = $this->request->attributes->get("_easy_faq_category");
        $categories = $this->categoryRepository->getPublished();
        $entriesQueryBuilder = $this->entryRepository->getByCategory($category, true);

        $pagerfanta = new Pagerfanta(
            new QueryAdapter($entriesQueryBuilder)
        );

        $breadcrumb->addRouteItem($category->getName(), ['route' => "easy_faq_category_index", 'params' => ['category' => $category->getSlug()]]);

        $args = [
            'categories' => $categories,
            'category' => $category,
            'entries'  => $pagerfanta,
            'breadcrumb' => $breadcrumb
        ];
        $event = new EasyFaqCategoryEvent($category, $args, $template);
        /**
         * @var EasyFaqCategoryEvent $result;
         */
        $result = $this->container->get("event_dispatcher")->dispatch($event, EasyFaqCategoryEvent::NAME);

        return $this->render($result->getTemplate(), $result->getArgs());
    }

    public function faqRoot(): Response
    {
        $template = '@EasyFaq/front/root.html.twig';
        $breadcrumb = $this->container->get('easy_seo.breadcrumb');
        $categories = $this->categoryRepository->getPublished();
        $entriesQueryBuilder = $this->entryRepository->getPublished(true);

        $pagerfanta = new Pagerfanta(
            new QueryAdapter($entriesQueryBuilder)
        );

        $args = [
            'categories' => $categories,
            'entries'  => $pagerfanta,
            'page'  => [
                'name' => null,
                'seo' => new SEO()
            ],
            'breadcrumb' => $breadcrumb
        ];
        $event = new EasyFaqCategoryEvent(null, $args, $template);
        /**
         * @var EasyFaqCategoryEvent $result;
         */
        $result = $this->container->get("event_dispatcher")->dispatch($event, EasyFaqCategoryEvent::NAME);

        return $this->render($result->getTemplate(), $result->getArgs());
    }
}
