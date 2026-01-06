<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\EventListener;

use Psr\Log\LoggerInterface;
use SBSEDV\Bundle\RequestIdBundle\Messenger\Stamp\RequestIdStamp;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class WorkerMessageReceivedEventListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestIdProviderInterface $requestIdProvider,
        private readonly ?LoggerInterface $logger,
    ) {
    }

    public function __invoke(WorkerMessageReceivedEvent $event): void
    {
        $stamp = $event->getEnvelope()->last(RequestIdStamp::class);
        if (null === $stamp) {
            return;
        }

        $this->requestIdProvider->setRequestId($stamp->requestId);

        $this->logger?->debug('[RequestID] Set Request-ID "{requestId}" from stamped envelope.', [
            'requestId' => $stamp->requestId,
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => ['__invoke', \PHP_INT_MAX],
        ];
    }
}
