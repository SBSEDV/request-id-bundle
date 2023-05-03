<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use SBSEDV\Bundle\RequestIdBundle\Twig\Extension\RequestIdExtension;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(RequestIdExtension::class)
            ->args([
                '$requestIdProvider' => service(RequestIdProviderInterface::class),
                '$functionName' => abstract_arg('The name of the registered twig function.'),
            ])
            ->tag('twig.extension')

    ;
};
