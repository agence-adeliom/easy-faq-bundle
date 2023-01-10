<?php

namespace Adeliom\EasyFaqBundle\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityNameSlugTrait;
use Adeliom\EasyCommonBundle\Traits\EntityPublishableTrait;
use Adeliom\EasyCommonBundle\Traits\EntityThreeStateStatusTrait;
use Adeliom\EasyCommonBundle\Traits\EntityTimestampableTrait;
use Adeliom\EasySeoBundle\Traits\EntitySeoTrait;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('slug')]
#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass(repositoryClass: \Adeliom\EasyFaqBundle\Repository\EntryRepository::class)]
class EntryEntity
{
    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private TimestampableConstruct;
    }
    use EntityNameSlugTrait;
    use EntityThreeStateStatusTrait {
        EntityThreeStateStatusTrait::__construct as private StateStatusConstruct;
    }
    use EntityPublishableTrait {
        EntityPublishableTrait::__construct as private PublishableConstruct;
    }
    use EntitySeoTrait {
        EntitySeoTrait::__construct as private SEOConstruct;
    }

    /**
     * @var CategoryEntity|null
     */
    protected $category;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    protected ?string $answer = null;

    #[ORM\Column(name: 'css', type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    protected ?string $css = null;

    #[ORM\Column(name: 'js', type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    protected ?string $js = null;

    public function __construct()
    {
        $this->TimestampableConstruct();
        $this->PublishableConstruct();
        $this->SEOConstruct();
        $this->StateStatusConstruct();
    }

    /**
     * @return CategoryEntity|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): void
    {
        $this->answer = $answer;
    }

    public function getCss(): ?string
    {
        return $this->css;
    }

    public function setCss(string $css): void
    {
        $this->css = $css;
    }

    public function getJs(): ?string
    {
        return $this->js;
    }

    public function setJs(string $js): void
    {
        $this->js = $js;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setSeoTitle(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        if (empty($this->getSEO()->title)) {
            $this->getSEO()->title = $this->getName();
        }
    }

    #[ORM\PreRemove]
    public function onRemove(PreRemoveEventArgs $event): void
    {
        $this->setState(ThreeStateStatusEnum::UNPUBLISHED());
        $this->setName($this->getName().'-'.$this->getId().'-deleted');
        $this->setSlug($this->getSlug().'-'.$this->getId().'-deleted');
    }
}
