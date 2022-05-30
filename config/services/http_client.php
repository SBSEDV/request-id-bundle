<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\HttpClient\HttpClientRequestIdLogger;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(HttpClientRequestIdLogger::class)
            ->args([
                '$client' => service('.inner'),
                '$logger' => service('logger'),
                '$headerNames' => abstract_arg('The headers names to log.'),
            ])
            ->decorate('http_client')
            // ->tag('http_client.client')
            ->tag('kernel.reset', ['method' => 'reset'])
            ->tag('monolog.logger', ['channel' => 'http_client'])
    ;
};
