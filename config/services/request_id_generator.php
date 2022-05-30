<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\Generator\RequestIdGenerator;
use SBSEDV\Bundle\RequestIdBundle\Generator\UuidRequestIdGenerator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(RequestIdGenerator::class)

        ->set(UuidRequestIdGenerator::class)
    ;
};
