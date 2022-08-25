<?php

namespace Adeliom\EasyFaqBundle\EventListener;

use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
    public function __construct(
        /**
         * @readonly
         */
        private UrlGeneratorInterface $urlGenerator,
        /**
         * @readonly
         */
        private EntryRepository $entryRepository,
        /**
         * @readonly
         */
        private CategoryRepository $categoryRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerFaqCategoriesUrls($event->getUrlContainer());
        $this->registerFaqEntrysUrls($event->getUrlContainer());
    }

    public function registerFaqCategoriesUrls(UrlContainerInterface $urls): void
    {
        $categories = $this->categoryRepository->getPublished();

        foreach ($categories as $category) {
            if ($category->getSEO()->sitemap) {
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

    public function registerFaqEntrysUrls(UrlContainerInterface $urls): void
    {
        $entries = $this->entryRepository->getPublished();

        foreach ($entries as $entry) {
            if ($entry->getSEO()->sitemap) {
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
