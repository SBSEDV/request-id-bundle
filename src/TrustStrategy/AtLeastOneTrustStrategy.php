<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Symfony\Component\HttpFoundation\Request;

class AtLeastOneTrustStrategy implements TrustStrategyInterface
{
    /**
     * @param iterable<TrustStrategyInterface> $trustStrategies
     */
    public function __construct(
        private iterable $trustStrategies
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function isTrustedRequestId(string $requestId, Request $request): bool
    {
        foreach ($this->trustStrategies as $trustStrategy) {
            if ($trustStrategy->isTrustedRequestId($requestId, $request)) {
                return true;
            }
        }

        return false;
    }
}
