<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Symfony\Component\HttpFoundation\Request;

class TrustedIncomingRequestIdStrategy implements TrustStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function isTrustedRequestId(string $requestId, Request $request): bool
    {
        return true;
    }
}
