<?php

namespace Adeliom\EasyFaqBundle\Event;

use Adeliom\EasyFaqBundle\Entity\BaseEntryEntity;
use Symfony\Contracts\EventDispatcher\Event;

class EasyFaqEntryEvent extends Event
{
    /**
     * @var string
     */
    public const NAME = "EasyFaq.entry.before_render";


    public function __construct(protected $entry, protected $args, protected $template)
    {
    }

    /**
     * @return BaseEntryEntity
     */
    public function getEntry(): BaseEntryEntity
    {
        return $this->entry;
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
