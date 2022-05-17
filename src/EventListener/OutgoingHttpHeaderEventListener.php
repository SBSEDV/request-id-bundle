<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\EventListener;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class OutgoingHttpHeaderEventListener implements EventSubscriberInterface
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
        private string $headerName,
    ) {
    }

    /**
     * Add a HTTP-Header with the unique Request-ID.
     *
     * @param ResponseEvent $event The "kernel.response" event.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $event->getResponse()->headers->set($this->headerName, $this->requestIdProvider->getCurrentRequestId());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => ['onKernelResponse', 4096],
        ];
    }
}
