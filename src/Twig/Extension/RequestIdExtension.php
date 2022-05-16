<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Twig\Extension;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RequestIdExtension extends AbstractExtension
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('request_id', $this->requestIdProvider->getCurrentRequestId(...)),
        ];
    }
}
