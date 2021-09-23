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
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $config;

    public function __construct(EntryRepository $entryRepository, CategoryRepository $categoryRepository, $config)
    {
        $this->entryRepository    = $entryRepository;
        $this->categoryRepository    = $categoryRepository;
        $this->config    = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
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
        $host = $request->getHost();

        if(strpos($path, $this->config["root_path"]) === false){
            return;
        }

        $prefixes = preg_split('~/~', $this->config["root_path"], -1, PREG_SPLIT_NO_EMPTY);
        /** @var EntryEntity[] $pages */
        $slugsArray = preg_split('~/~', $path, -1, PREG_SPLIT_NO_EMPTY);

        if($this->config["root_path"] != "/"){
            $slugsArray = array_values(array_diff($slugsArray, $prefixes));
        }

        if(!empty($slugsArray)) {
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
        }else{
            if(!empty($prefixes) && (count($slugsArray) === 0)){
                $event->getRequest()->attributes->set('_easy_faq_root', true);
            }
        }


    }
}
