<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Generator;

interface RequestIdGeneratorInterface
{
    /**
     * Create a new Request-ID.
     */
    public function createNewRequestId(): string;
}
