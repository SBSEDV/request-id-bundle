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
        private readonly RequestIdProviderInterface $requestIdProvider,
        private readonly string $headerName = 'x-request-id',
    ) {
    }

    /**
     * @param array<array-key, mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
        @$options['headers'][$this->headerName] = $this->requestIdProvider->getCurrentRequestId();

        return $this->client->request($method, $url, $options);
    }
}
