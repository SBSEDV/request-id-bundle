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
        return new AsyncResponse($this->client, $method, $url, $options, function (ChunkInterface $chunk, AsyncContext $context) {
            // @see https://github.com/symfony/symfony/pull/47990/commits/e100562fed39ee64fee523b2eacf6ec361429bad#r1004799422
            if ($context->getInfo('canceled') || $chunk->isTimeout() || null !== $chunk->getInformationalStatus()) {
                yield $chunk;

                return;
            }

            if ($chunk->isFirst()) {
                $headers = $context->getHeaders();

                foreach ($this->headerNames as $headerName) {
                    if (\array_key_exists($headerName, $headers)) {
                        foreach ($headers[$headerName] as $header) {
                            $info = $context->getInfo();

                            $this->logger->debug(\sprintf('Response %s, Request-ID: %s', $info['url'], $header));
                        }
                    }
                }
            }

            yield $chunk;
        });
    }
}
