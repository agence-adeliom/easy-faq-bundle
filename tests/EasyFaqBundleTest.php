<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests;

use Adeliom\EasyFaqBundle\DependencyInjection\EasyFaqExtension;
use Adeliom\EasyFaqBundle\EasyFaqBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyFaqBundle\EasyFaqBundle::class)]
final class EasyFaqBundleTest extends TestCase
{
    public function testBundleReturnsExpectedContainerExtension(): void
    {
        $bundle = new EasyFaqBundle();
        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(EasyFaqExtension::class, $extension);
        self::assertSame('easy_faq', $extension?->getAlias());
    }
}
