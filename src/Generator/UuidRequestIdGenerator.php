<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Generator;

use Symfony\Component\Uid\Uuid;

class UuidRequestIdGenerator implements RequestIdGeneratorInterface
{
    public function __construct(
        private readonly int $version = 4
    ) {
    }

    public function createNewRequestId(): string
    {
        $method = 'v'.$this->version;

        return (string) Uuid::$method();
    }
}
