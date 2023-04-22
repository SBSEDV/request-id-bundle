<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\EventListener;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\TrustStrategyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class IncomingHttpHeaderEventListener implements EventSubscriberInterface
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
        private string $headerName,
        private TrustStrategyInterface $trustStrategy
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
            return;
        }

        $this->requestIdProvider->setRequestId($requestId);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 4096],
        ];
    }
}
