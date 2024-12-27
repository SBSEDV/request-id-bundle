<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\EventListener;

use Psr\Log\LoggerInterface;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\TrustStrategyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class IncomingHttpHeaderEventListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestIdProviderInterface $requestIdProvider,
        private readonly string $headerName,
        private readonly TrustStrategyInterface $trustStrategy,
        private readonly ?LoggerInterface $logger,
    ) {
    }

    /**
     * Set the unique Request-ID from an incoming HTTP-Header.
     *
     * @param RequestEvent $event The "kernel.response" event.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $requestId = $event->getRequest()->headers->get($this->headerName);
        if (!\is_string($requestId)) {
            return;
        }

        if (!$this->trustStrategy->isTrustedRequestId($requestId, $event->getRequest())) {
            $this->logger?->debug('[RequestID] Untrusted Request-ID provided in incoming "{headerName}" header.', [
                'headerName' => $this->headerName,
            ]);

            return;
        }

        $this->logger?->debug('[RequestID] Reusing Request-ID from incoming "{headerName}" header.', [
            'headerName' => $this->headerName,
        ]);

        $this->requestIdProvider->setRequestId($requestId);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 4096],
        ];
    }
}
