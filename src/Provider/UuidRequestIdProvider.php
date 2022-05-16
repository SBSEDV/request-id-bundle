<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Provider;

use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Service\ResetInterface;

class UuidRequestIdProvider implements RequestIdProviderInterface, ResetInterface
{
    private string $requestId;

    public function __construct(
        private string $prefix = ''
    ) {
        $this->reset();
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        $this->requestId = $this->prefix.(string) Uuid::v6();
    }
}
