<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\FalseTrustStrategy;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\TrueTrustStrategy;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(TrueTrustStrategy::class)

        ->set(FalseTrustStrategy::class)
    ;
};
