<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

class IpBasedTrustStrategy implements TrustStrategyInterface
{
    /**
     * @param string|string[] $trustedIps
     */
    public function __construct(
        private string|array $trustedIps
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function isTrustedRequestId(string $requestId, Request $request): bool
    {
        $clientIp = $request->getClientIp();

        if (null === $clientIp) {
            return false;
        }

        return IpUtils::checkIp($clientIp, $this->trustedIps);
    }
}
