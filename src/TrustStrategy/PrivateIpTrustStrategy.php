<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

class PrivateIpTrustStrategy implements TrustStrategyInterface
{
    /**
     * @param string|string[]|null $subnets
     */
    public function __construct(
        private readonly string|array|null $subnets = null
    ) {
    }

    public function isTrustedRequestId(string $requestId, Request $request): bool
    {
        $clientIp = $request->getClientIp();

        if (null === $clientIp) {
            return false;
        }

        return IpUtils::checkIp($clientIp, $this->subnets ?? IpUtils::PRIVATE_SUBNETS);
    }
}
