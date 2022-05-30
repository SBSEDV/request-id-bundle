<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\Generator\RequestIdGeneratorInterface;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProvider;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(RequestIdProvider::class)
            ->args([
                '$requestIdGenerator' => service(RequestIdGeneratorInterface::class),
            ])
            ->tag('kernel.reset', ['method' => 'reset'])

        ->alias(RequestIdProviderInterface::class, RequestIdProvider::class)
    ;
};
