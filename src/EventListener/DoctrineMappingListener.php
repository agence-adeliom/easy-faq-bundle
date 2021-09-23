<?php

namespace Adeliom\EasyFaqBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This class adds automatically the ManyToOne and OneToMany relations in Page and Category entities,
 * because it's normally impossible to do so in a mapped superclass.
 */
class DoctrineMappingListener implements EventSubscriber
{
    /**
     * @var string
     */
    private $entryClass;

    /**
     * @var string
     */
    private $categoryClass;

    public function __construct(string $entryClass, string $categoryClass)
    {
        $this->entryClass = $entryClass;
        $this->categoryClass = $categoryClass;
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();


        $isEntry     = is_a($classMetadata->getName(), $this->entryClass, true);
        $isCategory = is_a($classMetadata->getName(), $this->categoryClass, true);

        if ($isEntry) {
            $this->processEntriesMetadata($classMetadata);
        }

        if ($isCategory) {
            $this->processCategoriesMetadata($classMetadata);
        }
    }

    private function processEntriesMetadata(ClassMetadata $classMetadata): void
    {
        if (!$classMetadata->hasAssociation('categories')) {
            $classMetadata->mapManyToMany([
                'fieldName' => 'categories',
                'targetEntity' => $this->categoryClass,
                'inversedBy' => 'entries',
                'cascade' => ['persist'],
                'joinTable' => [
                    'name' => "faq_categories_entries"
                ]
            ]);
        }
    }

    private function processCategoriesMetadata(ClassMetadata $classMetadata): void
    {
        if (!$classMetadata->hasAssociation('entries')) {
            $classMetadata->mapManyToMany([
                'fieldName' => 'entries',
                'targetEntity' => $this->entryClass,
                'mappedBy' => 'categories',
                'orphanRemoval' => false,
                'cascade' => ['persist'],
            ]);
        }
    }
}
