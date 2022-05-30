<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\EventListener\OutgoingHttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(OutgoingHttpHeaderEventListener::class)
            ->args([
                '$requestIdProvider' => service(RequestIdProviderInterface::class),
                '$headerName' => abstract_arg('The name of the incoming http header.'),
            ])
            ->tag('kernel.event_subscriber')
    ;
};
