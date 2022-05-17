<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\DependencyInjection;

use SBSEDV\Bundle\RequestIdBundle\EventListener\IncomingHttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\EventListener\OutgoingHttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\Generator\RequestIdGenerator;
use SBSEDV\Bundle\RequestIdBundle\Generator\RequestIdGeneratorInterface;
use SBSEDV\Bundle\RequestIdBundle\Generator\UuidRequestIdGenerator;
use SBSEDV\Bundle\RequestIdBundle\HttpClient\HttpClientRequestIdLogger;
use SBSEDV\Bundle\RequestIdBundle\Monolog\RequestIdLogProcessor;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProvider;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\FalseTrustStrategy;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\TrueTrustStrategy;
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
        $this->configureTrustStrategies($container, $config);
        $this->configureHttpClient($container, $config);
    }

    /**
     * Configure the main Request-ID provider service.
     */
    private function configureProvider(ContainerBuilder $container, array $config): void
    {
        switch ($config['generator']) {
            case RequestIdGenerator::class:
                $container
                    ->setDefinition(RequestIdGenerator::class, new Definition(RequestIdGenerator::class))
                ;
                break;

            case UuidRequestIdGenerator::class:
                $container
                    ->setDefinition(UuidRequestIdGenerator::class, new Definition(UuidRequestIdGenerator::class))
                ;
                break;

            default:
                // user specified a custom service
        }

        $container->setAlias(RequestIdGeneratorInterface::class, $config['generator']);

        $container
            ->setDefinition(RequestIdProvider::class, new Definition(RequestIdProvider::class))
            ->setArguments([
                '$requestIdGenerator' => new Reference(RequestIdGeneratorInterface::class),
            ])
            ->addTag('kernel.reset', ['method' => 'reset'])
        ;

        $container->setAlias(RequestIdProviderInterface::class, RequestIdProvider::class);
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
                '$trustStrategy' => new Reference($config['trust_incoming_http_header']),
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
                '$functionName' => $config['twig_function_name'],
            ])
            ->addTag('twig.extension')
        ;
    }

    /**
     * Configure the various incoming request ids trust resolvers.
     */
    private function configureTrustStrategies(ContainerBuilder $container, array $config): void
    {
        $container
            ->setDefinition(TrueTrustStrategy::class, new Definition(TrueTrustStrategy::class))
        ;

        $container
            ->setDefinition(FalseTrustStrategy::class, new Definition(FalseTrustStrategy::class))
        ;
    }

    private function configureHttpClient(ContainerBuilder $container, array $config): void
    {
        if (!$config['http_client']['enabled']) {
            return;
        }

        $container
            ->setDefinition(HttpClientRequestIdLogger::class, new Definition(HttpClientRequestIdLogger::class))
            ->setArguments([
                '$client' => new Reference('.inner'),
                '$logger' => new Reference('logger'),
                '$headerNames' => $config['http_client']['header_names'],
            ])
            ->setDecoratedService('http_client')
            ->addTag('http_client.client')
            ->addTag('kernel.reset', ['method' => 'reset'])
            ->addTag('monolog.logger', ['channel' => 'http_client'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        // process the configuration
        $configs = $container->getExtensionConfig($this->getAlias());

        // resolve config parameters e.g. %kernel.debug% to its boolean value
        $resolvingBag = $container->getParameterBag();
        $configs = $resolvingBag->resolveValue($configs);

        // use the Configuration class to generate a config array
        $config = $this->processConfiguration(new Configuration(), $configs);

        $bundles = $container->getParameter('kernel.bundles');

        if (\in_array(TwigBundle::class, $bundles, true)) {
            if ($config['twig_error_template'] === true) {
                $thirdPartyBundlesViewFileLocator = (new FileLocator(__DIR__.'/../Resources/views/bundles'));

                $container->loadFromExtension('twig', [
                    'paths' => [
                        $thirdPartyBundlesViewFileLocator->locate('TwigBundle') => 'Twig', // @phpstan-ignore-line
                    ],
                ]);
            }
        }
    }
}
