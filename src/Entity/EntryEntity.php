<?php

namespace Adeliom\EasyFaqBundle\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityNameSlugTrait;
use Adeliom\EasyCommonBundle\Traits\EntityPublishableTrait;
use Adeliom\EasyCommonBundle\Traits\EntityThreeStateStatusTrait;
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
class EntryEntity {

    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private __TimestampableConstruct;
    }

    use EntityNameSlugTrait;
    use EntityThreeStateStatusTrait;
    use EntityPublishableTrait {
        EntityPublishableTrait::__construct as private __PublishableConstruct;
    }

    use EntitySeoTrait {
        EntitySeoTrait::__construct as private __SEOConstruct;
    }

    /**
     * @var CategoryEntity[] | null
     */
    protected $categories;

    /**
     * @var string | null
     * @ORM\Column(type="string")
     */
    protected $question;

    /**
     * @var string | null
     * @ORM\Column(type="text")
     */
    protected $answer;

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
        $this->__PublishableConstruct();
        $this->__SEOConstruct();
        $this->categories = new ArrayCollection();
    }

    /**
     * @return CategoryEntity|null
     */
    public function getMainCategory()
    {
        return !empty($this->categories) && isset($this->categories[0]) ? $this->categories[0] : null;
    }

    /**
     * @return BaseCategoryEntity[]|null
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param CategoryEntity[]|null $categories
     */
    public function setCategories(?array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @param CategoryEntity $categoryEntity
     *
     * @return EntryEntity
     */
    public function addCategorie(CategoryEntity $categoryEntity)
    {
        if (!$this->categories->contains($categoryEntity)) {
            $this->categories->add($categoryEntity);
        }

        return $this;
    }

    /**
     * @param CategoryEntity $categoryEntity
     *
     * @return EntryEntity
     */
    public function removeCategory(CategoryEntity $categoryEntity)
    {
        if ($this->categories->contains($categoryEntity)) {
            $this->categories->removeElement($categoryEntity);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * @param string|null $question
     */
    public function setQuestion(?string $question): void
    {
        $this->question = $question;
    }

    /**
     * @return string|null
     */
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * @param string|null $answer
     */
    public function setAnswer(?string $answer): void
    {
        $this->answer = $answer;
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
        $this->setState(ThreeStateStatusEnum::UNPUBLISHED());
        $this->setName($this->getName() . '-'.$this->getId().'-deleted');
        $this->setSlug($this->getSlug() . '-'.$this->getId().'-deleted');
    }
}
