<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Provider;

use Monolog\ResettableInterface;

interface RequestIdProviderInterface extends ResettableInterface
{
    /**
     * Get the current unique request id.
     */
    public function getCurrentRequestId(): string;
}
