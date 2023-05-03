<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\HttpClient;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestIdSettingHttpClient implements HttpClientInterface
{
    use DecoratorTrait;

    public function __construct(
        private HttpClientInterface $client,
        private RequestIdProviderInterface $requestIdProvider,
        private string $headerName = 'x-request-id'
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        @$options['headers'][$this->headerName] = $this->requestIdProvider->getCurrentRequestId();

        return $this->request($method, $url, $options);
    }
}
