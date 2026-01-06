<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class RequestIdStamp implements StampInterface
{
    public function __construct(
        public readonly string $requestId,
    ) {
    }
}
