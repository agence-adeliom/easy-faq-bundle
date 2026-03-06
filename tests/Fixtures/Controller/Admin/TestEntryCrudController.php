<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Fixtures\Controller\Admin;

use Adeliom\EasyFaqBundle\Controller\EntryCrudController;

final class TestEntryCrudController extends EntryCrudController
{
    public static function getEntityFqcn(): string
    {
        return \stdClass::class;
    }
}
