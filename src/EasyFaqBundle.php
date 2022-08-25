<?php

namespace Adeliom\EasyFaqBundle;

use Adeliom\EasyFaqBundle\DependencyInjection\EasyFaqExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyFaqBundle extends Bundle
{
    /**
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new EasyFaqExtension();
    }
}
