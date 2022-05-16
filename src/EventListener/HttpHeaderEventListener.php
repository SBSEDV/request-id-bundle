<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\EventListener;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class HttpHeaderEventListener implements EventSubscriberInterface
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
        private ?string $incomingHeaderName,
        private ?string $outgoingHeaderName,
    ) {
    }

    /**
     * Set the unique Request-ID from an incoming HTTP-Header.
     *
     * @param ResponseEvent $event The "kernel.response" event.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (null === $this->incomingHeaderName || !$event->isMainRequest()) {
            return;
        }

        $incoming = $event->getRequest()->headers->get($this->incomingHeaderName);

        if (\is_string($incoming)) {
            $this->requestIdProvider->setRequestId($incoming);
        }
    }

    /**
     * Add a HTTP-Header with the unique Request-ID.
     *
     * @param ResponseEvent $event The "kernel.response" event.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (null === $this->outgoingHeaderName || !$event->isMainRequest()) {
            return;
        }

        $event->getResponse()->headers->set($this->outgoingHeaderName, $this->requestIdProvider->getCurrentRequestId());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 4096],
            ResponseEvent::class => ['onKernelResponse', 4096],
        ];
    }
}
