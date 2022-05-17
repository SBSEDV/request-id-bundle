<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Provider;

use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Service\ResetInterface;

class UuidRequestIdProvider implements RequestIdProviderInterface, ResetInterface
{
    private ?string $requestId = null;

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
        $this->requestId = (string) Uuid::v6();
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }
}
