<?php

namespace Adeliom\EasyFaqBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Adeliom\EasyFaqBundle\DependencyInjection\EasyFaqExtension;

class EasyFaqBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new EasyFaqExtension();
    }
}
