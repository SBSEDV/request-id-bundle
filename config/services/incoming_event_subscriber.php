<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\EventListener\IncomingHttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(IncomingHttpHeaderEventListener::class)
            ->args([
                '$requestIdProvider' => service(RequestIdProviderInterface::class),
                '$headerName' => abstract_arg('The name of the incoming http header.'),
                '$trustStrategy' => abstract_arg('The trust strategy service.'),
                '$logger' => service('logger')->nullOnInvalid(),
            ])
            ->tag('kernel.event_subscriber')
    ;
};
