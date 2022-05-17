<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class HashHmacRequestIdStrategy implements IncomingRequestIdStrategyInterface
{
    public function __construct(
        private string $key,
        private string $algorithm,
        private string $headerName,
        private LoggerInterface $logger
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function isTrustedRequestId(string $requestId, Request $request): bool
    {
        $signature = $request->headers->get($this->headerName);

        if (!\is_string($signature)) {
            $this->logger->debug('Incoming Request-ID signature is missing. A new Request-ID will be generated.');

            return false;
        }

        $expected = $this->createSignature($requestId);
        if (!\hash_equals($expected, $signature)) {
            $this->logger->debug('Incoming Request-ID signature is invalid. A new Request-ID will be generated.', [
                'signature' => $signature,
                'expected' => $expected,
            ]);

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    private function createSignature(string $requestId): string
    {
        return \hash_hmac($this->algorithm, $requestId, $this->key);
    }
}
