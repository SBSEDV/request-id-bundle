<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Provider;

use Monolog\ResettableInterface;
use Symfony\Contracts\Service\ResetInterface;

interface RequestIdProviderInterface extends ResettableInterface, ResetInterface
{
    /**
     * Get the current request id.
     */
    public function getCurrentRequestId(): string;

    /**
     * Set the request id of the current request.
     */
    public function setRequestId(string $requestId): void;
}
