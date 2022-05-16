<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Provider;

use Symfony\Contracts\Service\ResetInterface;

class RequestIdProvider implements RequestIdProviderInterface, ResetInterface
{
    public const DEFAULT_LENGTH = 16;

    private string $requestId;

    public function __construct(
        private int $length = self::DEFAULT_LENGTH,
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
        $this->requestId = $this->prefix.\substr(\bin2hex(\random_bytes((int) \ceil($this->length / 2))), 0, $this->length); // @phpstan-ignore-line
    }
}
