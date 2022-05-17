<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Symfony\Component\HttpFoundation\Request;

class RDNSTrustStrategy implements TrustStrategyInterface
{
    public function __construct(
        private array $hostnames
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function isTrustedRequestId(string $requestId, Request $request): bool
    {
        $clientIp = $request->getClientIp();

        if ($clientIp === null) {
            return false;
        }

        $host = \gethostbyaddr($clientIp);

        if (false === $host || $clientIp === $host) {
            return false;
        }

        foreach ($this->hostnames as $hostname) {
            if ($hostname === $host) {
                return true;
            }
        }

        return false;
    }
}
