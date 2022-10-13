<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\HttpClient;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClientRequestIdLogger implements HttpClientInterface
{
    use AsyncDecoratorTrait;

    public function __construct(
        HttpClientInterface $client,
        private LoggerInterface $logger,
        private array $headerNames
    ) {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return new AsyncResponse($this->client, $method, $url, $options, function (ChunkInterface $chunk, AsyncContext $asyncContext) {
            if ($chunk->isTimeout() || null !== $chunk->getInformationalStatus() || $asyncContext->getInfo('canceled')) {
                yield $chunk;

                return;
            }

            if ($chunk->isFirst()) {
                $headers = $asyncContext->getHeaders();

                foreach ($this->headerNames as $headerName) {
                    if (\array_key_exists($headerName, $headers)) {
                        foreach ($headers[$headerName] as $header) {
                            $info = $asyncContext->getInfo();

                            $this->logger->debug(\sprintf('Response %s, Request-ID: %s', $info['url'], $header));
                        }
                    }
                }
            }

            yield $chunk;
        });
    }
}
