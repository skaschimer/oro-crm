<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroTestFrameworkExtension extends Extension
{
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
    }
}
