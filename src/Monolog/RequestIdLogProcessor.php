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
        private readonly RequestIdProviderInterface $requestIdProvider,
        private readonly string $key,
    ) {
    }

    public function __invoke(array|LogRecord $record): array|LogRecord
    {
        $record['extra'][$this->key] = $this->requestIdProvider->getCurrentRequestId();

        return $record;
    }

    public function reset(): void
    {
        $this->requestIdProvider->reset();
    }
}
