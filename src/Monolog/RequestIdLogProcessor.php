<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Monolog;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Monolog\ResettableInterface;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use Symfony\Contracts\Service\ResetInterface;

class RequestIdLogProcessor implements ProcessorInterface, ResettableInterface, ResetInterface
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
        private string $key
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array|LogRecord $record): array|LogRecord
    {
        $record['extra'][$this->key] = $this->requestIdProvider->getCurrentRequestId();

        return $record;
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        if ($this->requestIdProvider instanceof ResettableInterface) {
            $this->requestIdProvider->reset();
        }
    }
}
