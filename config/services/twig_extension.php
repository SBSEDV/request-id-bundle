<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\FalseTrustStrategy;
use SBSEDV\Bundle\RequestIdBundle\Twig\Extension\RequestIdExtension;
use Symfony\Contracts\Translation\TranslatorInterface;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(RequestIdExtension::class)
            ->args([
                '$requestIdProvider' => service(RequestIdProviderInterface::class),
                '$translator' => service(TranslatorInterface::class)->nullOnInvalid(),
                '$functionName' => abstract_arg('The name of the registered twig function.'),
            ])
            ->tag('twig.extension')

        ->set(FalseTrustStrategy::class)
    ;
};
