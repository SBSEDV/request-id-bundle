<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

class PrivateIpTrustStrategy implements TrustStrategyInterface
{
    // @see https://github.com/symfony/http-client/blob/6.1/NoPrivateNetworkHttpClient.php
    private const PRIVATE_SUBNETS = [
        '127.0.0.0/8',
        '10.0.0.0/8',
        '192.168.0.0/16',
        '172.16.0.0/12',
        '169.254.0.0/16',
        '0.0.0.0/8',
        '240.0.0.0/4',
        '::1/128',
        'fc00::/7',
        'fe80::/10',
        '::ffff:0:0/96',
        '::/128',
    ];

    public function __construct(
        private string|array|null $subnets = null
    ) {
    }

    public function isTrustedRequestId(string $requestId, Request $request): bool
    {
        $clientIp = $request->getClientIp();

        if (null === $clientIp) {
            return false;
        }

        return IpUtils::checkIp($clientIp, $this->subnets ?? self::PRIVATE_SUBNETS);
    }
}
