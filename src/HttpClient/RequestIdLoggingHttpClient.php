<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\HttpClient;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestIdLoggingHttpClient implements HttpClientInterface
{
    use AsyncDecoratorTrait;

    /**
     * @param string[] $headerNames
     */
    public function __construct(
        HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly array $headerNames,
    ) {
        $this->client = $client;
    }

    /**
     * @param array<array-key, mixed> $options
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

                            if (\is_array($info) && \array_key_exists('url', $info)) {
                                $this->logger->debug('Response {url}, Request-ID: {requestId}', [
                                    'url' => $info['url'],
                                    'requestId' => $header,
                                ]);
                            }
                        }
                    }
                }
            }

            yield $chunk;
        });
    }
}
