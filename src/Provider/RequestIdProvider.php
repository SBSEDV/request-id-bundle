<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Provider;

use SBSEDV\Bundle\RequestIdBundle\Generator\RequestIdGeneratorInterface;

class RequestIdProvider implements RequestIdProviderInterface
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

        return $this->requestId; // @phpstan-ignore-line
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        $this->requestId = $this->requestIdGenerator->createNewRequestId();
    }
}
