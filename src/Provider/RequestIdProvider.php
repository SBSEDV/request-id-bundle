<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Provider;

use SBSEDV\Bundle\RequestIdBundle\Generator\RequestIdGeneratorInterface;
use Symfony\Contracts\Service\ResetInterface;

class RequestIdProvider implements RequestIdProviderInterface, ResetInterface
{
    private ?string $requestId = null;

    public function __construct(
        private RequestIdGeneratorInterface $requestIdGenerator
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentRequestId(): string
    {
        if (null === $this->requestId) {
            $this->reset();
        }

        return $this->requestId;
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        $this->requestId = $this->requestIdGenerator->createNewRequestId();
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }
}
