<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\Monolog\RequestIdLogProcessor;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(RequestIdLogProcessor::class)
            ->args([
                '$requestIdProvider' => service(RequestIdProviderInterface::class),
                '$key' => abstract_arg('The monolog extra key that will contain the request id.'),
            ])
            ->tag('kernel.reset', ['method' => 'reset'])
            ->tag('monolog.processor')
    ;
};
