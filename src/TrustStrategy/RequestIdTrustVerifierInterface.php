<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\TrustStrategy;

use Symfony\Component\HttpFoundation\Request;

interface RequestIdTrustVerifierInterface
{
    /**
     * Whether the incoming Request-ID is trusted and should be used.
     *
     * @param string  $requestId The Request-ID to verify.
     * @param Request $request   The http-foundation request.
     */
    public function isTrustedRequestId(string $requestId, Request $request): bool;
}
