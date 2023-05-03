<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\PrivateIpTrustStrategy;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\TrustStrategyInterface;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set(PrivateIpTrustStrategy::class)

        ->alias(TrustStrategyInterface::class, PrivateIpTrustStrategy::class)
    ;
};
