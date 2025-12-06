<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Generator;

class RequestIdGenerator implements RequestIdGeneratorInterface
{
    public function __construct(
        private readonly int $length = 16,
    ) {
    }

    public function createNewRequestId(): string
    {
        return \substr(\bin2hex(\random_bytes((int) \ceil($this->length / 2))), 0, $this->length);  // @phpstan-ignore-line argument.type
    }
}
