<?php

namespace Adeliom\EasyFaqBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Adeliom\EasyFaqBundle\DependencyInjection\EasyFaqExtension;

class EasyFaqBundle extends Bundle
{
    /**
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension()
    {
        return new EasyFaqExtension();
    }
}
