<?php

namespace Adeliom\EasyFaqBundle\Entity;

use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityNameSlugTrait;
use Adeliom\EasyCommonBundle\Traits\EntityStatusTrait;
use Adeliom\EasyCommonBundle\Traits\EntityTimestampableTrait;
use Adeliom\EasySeoBundle\Traits\EntitySeoTrait;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @UniqueEntity("slug")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\MappedSuperclass(repositoryClass="Adeliom\EasyFaqBundle\Repository\EntryRepository")
 */
class CategoryEntity {

    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private __TimestampableConstruct;
    }

    use EntityNameSlugTrait;
    use EntityStatusTrait;
    use EntitySeoTrait {
        EntitySeoTrait::__construct as private __SEOConstruct;
    }

    /**
     * @var BaseEntryEntity[] | null
     */
    protected $entries;

    /**
     * @var string|null
     *
     * @ORM\Column(name="css", type="text", nullable=true)
     * @Assert\Type("string")
     */
    protected $css;

    /**
     * @var string|null
     *
     * @ORM\Column(name="js", type="text", nullable=true)
     * @Assert\Type("string")
     */
    protected $js;

    public function __construct()
    {
        $this->__TimestampableConstruct();
        $this->__SEOConstruct();
        $this->entries     = new ArrayCollection();
    }

    /**
     * @return EntryEntity[]|ArrayCollection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    public function addEntry(EntryEntity $entry): void
    {
        $this->entries->add($entry);
        if ($entry->getCategory() !== $this) {
            $entry->setCategory($this);
        }
    }

    public function removeEntry(EntryEntity $entry): void
    {
        $this->entries->removeElement($entry);
        $entry->setCategory(null);
    }

    /**
     * @return string|null
     */
    public function getCss(): ?string
    {
        return $this->css;
    }

    /**
     * @param string $css
     */
    public function setCss(string $css): void
    {
        $this->css = $css;
    }

    /**
     * @return string|null
     */
    public function getJs(): ?string
    {
        return $this->js;
    }

    /**
     * @param string $js
     */
    public function setJs(string $js): void
    {
        $this->js = $js;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setSeoTitle(LifecycleEventArgs $event): void
    {
        if(empty($this->getSEO()->title)){
            $this->getSEO()->title = $this->getName();
        }
    }

    /**
     * @ORM\PreRemove()
     */
    public function onRemove(LifecycleEventArgs $event): void
    {
        $this->setStatus(false);
        $this->setName($this->getName() . '-'.$this->getId().'-deleted');
        $this->setSlug($this->getSlug() . '-'.$this->getId().'-deleted');
    }
}
