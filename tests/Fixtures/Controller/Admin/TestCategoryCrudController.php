<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Admin;

use Adeliom\EasyFaqBundle\Controller\CategoryCrudController;

final class TestCategoryCrudController extends CategoryCrudController
{
    public static function getEntityFqcn(): string
    {
        return \stdClass::class;
    }
}
