<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Symfony\Component\HttpFoundation\Request;

class ChainTrustStrategy implements TrustStrategyInterface
{
    /**
     * @param iterable<TrustStrategyInterface> $trustStrategies
     */
    public function __construct(
        private readonly iterable $trustStrategies,
    ) {
    }

    public function isTrustedRequestId(string $requestId, Request $request): bool
    {
        foreach ($this->trustStrategies as $trustStrategy) {
            if (!$trustStrategy->isTrustedRequestId($requestId, $request)) {
                return false;
            }
        }

        return true;
    }
}
