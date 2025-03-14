<?php

namespace Adeliom\EasyFaqBundle\EventListener;

use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Adeliom\EasyFaqBundle\Entity\EntryEntity;
use Adeliom\EasyFaqBundle\Repository\CategoryRepository;
use Adeliom\EasyFaqBundle\Repository\EntryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EntryListener implements EventSubscriberInterface
{
    /**
     * @param mixed[] $config
     */
    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly CategoryRepository $categoryRepository,
        private $config
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['setRequestLayout', 33],
        ];
    }

    public function setRequestLayout(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Get the necessary informations to check them in layout configurations
        $path = $request->getPathInfo();

        if (!str_contains($path, (string) $this->config['root_path'])) {
            return;
        }

        $prefixes = preg_split('#/#', (string) $this->config['root_path'], -1, PREG_SPLIT_NO_EMPTY);
        /** @var EntryEntity[] $pages */
        $slugsArray = preg_split('#/#', $path, -1, PREG_SPLIT_NO_EMPTY);

        if ('/' !== $this->config['root_path']) {
            $slugsArray = array_values(array_diff($slugsArray, $prefixes));
        }

        if ($slugsArray !== [] && $slugsArray !== false) {
            $category = $this->categoryRepository->getBySlug($slugsArray[0]);
            if ($category instanceof CategoryEntity) {
                $event->getRequest()->attributes->set('_easy_faq_category', $category);

                if (isset($slugsArray[1])) {
                    $entry = $this->entryRepository->getBySlug($slugsArray[1], $category);
                    if ($entry instanceof EntryEntity) {
                        $event->getRequest()->attributes->set('_easy_faq_entry', $entry);
                    }
                }
            }
        } elseif ($prefixes !== [] && $prefixes !== false && ((is_countable($slugsArray) ? count($slugsArray) : 0) === 0)) {
            $event->getRequest()->attributes->set('_easy_faq_root', true);
        }
    }
}
