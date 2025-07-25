<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CaseBundle\DependencyInjection\OroCaseExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroCaseExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroCaseExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }
}
