<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\DependencyInjection;

use SBSEDV\Bundle\RequestIdBundle\EventListener\IncomingHttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\EventListener\OutgoingHttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\Monolog\RequestIdLogProcessor;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProvider;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use SBSEDV\Bundle\RequestIdBundle\Provider\UuidRequestIdProvider;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\HashHmacRequestIdStrategy;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\TrustedIncomingRequestIdStrategy;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\UntrustedRequestIdStrategy;
use SBSEDV\Bundle\RequestIdBundle\Twig\Extension\RequestIdExtension;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;

class SBSEDVRequestIdExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->configureProvider($container, $config);
        $this->configureEventSubscriber($container, $config);
        $this->configureMonologProcessor($container, $config);
        $this->configureTwigExtension($container, $config);
        $this->configureRequestIdTrustStrategies($container, $config);
    }

    /**
     * Configure the main Request-ID provider service.
     */
    private function configureProvider(ContainerBuilder $container, array $config): void
    {
        switch ($config['provider']) {
            case 'sbsedv_request_id.provider.default':
                $container
                    ->setDefinition('sbsedv_request_id.provider.default', new Definition(RequestIdProvider::class))
                    ->setArguments([
                        '$length' => $config['default_provider']['id_length'],
                    ])
                    ->addTag('kernel.reset', ['method' => 'reset'])
                    ->setPublic(true)
                ;
                break;

            case 'sbsedv_request_id.provider.uuid':
                $container
                    ->setDefinition('sbsedv_request_id.provider.uuid', new Definition(UuidRequestIdProvider::class))
                    ->addTag('kernel.reset', ['method' => 'reset'])
                ;
                break;

            default:
                // user specified a custom service
        }

        $container->setAlias(RequestIdProviderInterface::class, $config['provider']);
    }

    /**
     * Configure the optional event subscriber.
     */
    private function configureEventSubscriber(ContainerBuilder $container, array $config): void
    {
        $container
            ->setDefinition('sbsedv_request_id.event_listener.incoming_http_header', new Definition(IncomingHttpHeaderEventListener::class))
            ->setArguments([
                '$requestIdProvider' => new Reference(RequestIdProviderInterface::class),
                '$headerName' => $config['incoming_http_header'],
                '$incomingRequestIdStrategy' => new Reference($config['trust_incoming_http_header']),
            ])
            ->addTag('kernel.event_subscriber')
        ;

        $outgoingHeaderName = $config['outgoing_http_header'] ?? null;
        if (\is_string($outgoingHeaderName)) {
            $container
                ->setDefinition('sbsedv_request_id.event_listener.outgoing_http_header', new Definition(OutgoingHttpHeaderEventListener::class))
                ->setArguments([
                    '$requestIdProvider' => new Reference(RequestIdProviderInterface::class),
                    '$headerName' => $outgoingHeaderName,
                ])
                ->addTag('kernel.event_subscriber')
            ;
        }
    }

    /**
     * Configure the optional monolog processor.
     */
    private function configureMonologProcessor(ContainerBuilder $container, array $config): void
    {
        if (@$config['monolog_processor']['enabled'] !== true || !\in_array(MonologBundle::class, $container->getParameter('kernel.bundles'), true)) {
            return;
        }

        $container
            ->setDefinition('sbsedv_request_id.monolog_log_processor', new Definition(RequestIdLogProcessor::class))
            ->setArguments([
                '$requestIdProvider' => new Reference(RequestIdProviderInterface::class),
                '$key' => $config['monolog_processor']['key'],
            ])
            ->addTag('monolog.processor')
            ->addTag('kernel.reset', ['method' => 'reset'])
        ;
    }

    /**
     * Configure the optional twig extension.
     */
    private function configureTwigExtension(ContainerBuilder $container, array $config): void
    {
        // skip if the twig bundle is not installed
        if (!\in_array(TwigBundle::class, $container->getParameter('kernel.bundles'), true)) {
            return;
        }

        $container
            ->setDefinition('sbsedv_request_id.twig_extension', new Definition(RequestIdExtension::class))
            ->setArguments([
                '$requestIdProvider' => new Reference(RequestIdProviderInterface::class),
            ])
            ->addTag('twig.extension')
        ;
    }

    /**
     * Configure the various incoming request ids trust resolvers.
     */
    private function configureRequestIdTrustStrategies(ContainerBuilder $container, array $config): void
    {
        $container
            ->setDefinition('sbsedv_request_id.incoming_trust_strategies.trusted', new Definition(TrustedIncomingRequestIdStrategy::class))
        ;
        $container
            ->setDefinition('sbsedv_request_id.incoming_trust_strategies.untrusted', new Definition(UntrustedRequestIdStrategy::class))
        ;

        $container
            ->setDefinition('sbsedv_request_id.trust_strategies.hash_hmac', new Definition(HashHmacRequestIdStrategy::class))
            ->setArguments([
                '$key' => $config['hash_hmac_trust_strategy']['key'],
                '$algorithm' => $config['hash_hmac_trust_strategy']['algorithm'],
                '$headerName' => $config['hash_hmac_trust_strategy']['http_header'],
                '$logger' => new Reference('logger'),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('twig')) {
            return;
        }

        $configs = $container->getExtensionConfig('sbsedv_request_id');

        $isEnabled = true;
        foreach ($configs as $config) {
            if (\array_key_exists('twig_error_template', $config)) {
                $isEnabled = (bool) $config['twig_error_template'];
            }
        }

        if (!$isEnabled) {
            return;
        }

        $thirdPartyBundlesViewFileLocator = (new FileLocator(__DIR__.'/../Resources/views/bundles'));

        $container->loadFromExtension('twig', [
            'paths' => [
                $thirdPartyBundlesViewFileLocator->locate('TwigBundle') => 'Twig', // @phpstan-ignore-line
            ],
        ]);
    }
}
