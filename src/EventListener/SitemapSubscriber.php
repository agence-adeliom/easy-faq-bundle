<?php

namespace Adeliom\EasyFaqBundle\EventListener;

use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param EntryRepository  $entryRepository
     * @param CategoryRepository  $categoryRepository
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, EntryRepository $entryRepository, CategoryRepository $categoryRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->entryRepository = $entryRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerFaqCategoriesUrls($event->getUrlContainer());
        $this->registerFaqEntrysUrls($event->getUrlContainer());
    }

    /**
     * @param UrlContainerInterface $urls
     */
    public function registerFaqCategoriesUrls(UrlContainerInterface $urls): void
    {
        $categories = $this->categoryRepository->getPublished();

        foreach ($categories as $category) {
            if($category->getSEO()->sitemap) {
                $urls->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            'easy_faq_index',
                            ['slugs' => $category->getTree()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    'faq'
                );
            }
        }
    }

    /**
     * @param UrlContainerInterface $urls
     */
    public function registerFaqEntrysUrls(UrlContainerInterface $urls): void
    {
        $entries = $this->entryRepository->getPublished();

        foreach ($entries as $entry) {
            if($entry->getSEO()->sitemap) {
                $urls->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            'easy_faq_index',
                            ['slugs' => $entry->getTree()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    'faq'
                );
            }
        }
    }
}
