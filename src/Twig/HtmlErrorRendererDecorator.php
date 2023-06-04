<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Twig;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class HtmlErrorRendererDecorator implements ErrorRendererInterface
{
    public function __construct(
        private readonly ErrorRendererInterface $inner,
        private readonly RequestIdProviderInterface $requestIdProvider
    ) {
    }

    public function render(\Throwable $exception): FlattenException
    {
        $e = $this->inner->render($exception);

        $html = $e->getAsString();

        if (!\str_contains($html, '<body>')) {
            return $e;
        }

        $pos = \stripos($html, '</body>');
        if (false === $pos) {
            return $e;
        }

        // .container because of the default HTMLErrorRenderer template
        $insert = '<span class="container">Request-ID: '.$this->requestIdProvider->getCurrentRequestId().'</span>';

        $e->setAsString(\substr($html, 0, $pos).$insert.\substr($html, $pos));

        return $e;
    }
}
