<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use SBSEDV\Bundle\RequestIdBundle\Twig\HtmlErrorRendererDecorator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(HtmlErrorRendererDecorator::class)
            ->decorate('error_renderer')
            ->args([
                '$inner' => service('.inner'),
                '$requestIdProvider' => service(RequestIdProviderInterface::class),
            ])
    ;
};
