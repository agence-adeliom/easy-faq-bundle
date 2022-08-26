<?php

namespace Adeliom\EasyFaqBundle\Event;

use Adeliom\EasyFaqBundle\Entity\CategoryEntity;
use Symfony\Contracts\EventDispatcher\Event;

class EasyFaqCategoryEvent extends Event
{
    /**
     * @var string
     */
    public const NAME = 'EasyFaq.category.before_render';

    public function __construct(protected ?CategoryEntity $category, protected $args, protected $template)
    {
    }

    public function getEntry(): ?CategoryEntity
    {
        return $this->category;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return mixed
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
