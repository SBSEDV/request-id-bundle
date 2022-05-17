<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Generator;

use Symfony\Component\Uid\Uuid;

class UuidRequestIdGenerator implements RequestIdGeneratorInterface
{
    public function __construct(
        private int $version = 4
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createNewRequestId(): string
    {
        $method = 'v'.$this->version;

        return (string) Uuid::$method();
    }
}
