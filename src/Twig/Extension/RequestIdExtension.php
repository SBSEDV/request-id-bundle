<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\Twig\Extension;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RequestIdExtension extends AbstractExtension
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
        private ?TranslatorInterface $translator,
        private string $functionName
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction($this->functionName, $this->getCurrentRequestId(...)),
        ];
    }

    public function getCurrentRequestId(bool $withPrefix = false): string
    {
        $prefix = '';

        if ($withPrefix) {
            if (null === $this->translator) {
                throw new \LogicException('To use the $withPrefix option, you have to run composer require symfony/translation first.');
            }

            $prefix = $this->translator->trans('request_id_prefix', [], 'sbsedv_request_id');
        }

        return $prefix.$this->requestIdProvider->getCurrentRequestId();
    }
}
