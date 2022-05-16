<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\DependencyInjection;

use SBSEDV\Bundle\RequestIdBundle\EventListener\HttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\Monolog\RequestIdLogProcessor;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProvider;
use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProviderInterface;
use SBSEDV\Bundle\RequestIdBundle\Provider\UuidRequestIdProvider;
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

        switch ($config['provider']) {
            case 'sbsedv_request_id.provider.default':
                $container
                    ->setDefinition('sbsedv_request_id.provider.default', new Definition(RequestIdProvider::class))
                    ->setArguments([
                        '$length' => $config['default_provider']['id_length'],
                        '$prefix' => $config['prefix'],
                    ])
                    ->addTag('kernel.reset', ['method' => 'reset'])
                    ->setPublic(true)
                ;
                break;

            case 'sbsedv_request_id.provider.uuid':
                $container
                    ->setDefinition('sbsedv_request_id.provider.uuid', new Definition(UuidRequestIdProvider::class))
                    ->setArguments([
                        '$prefix' => $config['prefix'],
                    ])
                    ->addTag('kernel.reset', ['method' => 'reset'])
                ;
                break;

            default:
                // user specified a custom service
        }

        $container->setAlias(RequestIdProviderInterface::class, $config['provider']);

        if (\is_string($config['outgoing_http_header'] ?? null) || \is_string($config['incoming_http_header'] ?? null)) {
            $container
                ->setDefinition('sbsedv_request_id.event_listener.http_header', new Definition(HttpHeaderEventListener::class))
                ->setArguments([
                    '$requestIdProvider' => new Reference(RequestIdProviderInterface::class),
                    '$incomingHeaderName' => $config['incoming_http_header'],
                    '$outgoingHeaderName' => $config['outgoing_http_header'],
                ])
                ->addTag('kernel.event_subscriber')
            ;
        }

        $bundles = $container->getParameter('kernel.bundles');
        if (\in_array(MonologBundle::class, $bundles, true) && @$config['monolog_processor']['enabled'] === true) {
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
        if (\in_array(TwigBundle::class, $bundles, true)) {
            $container
                ->setDefinition('sbsedv_request_id.twig_extension', new Definition(RequestIdExtension::class))
                ->setArguments([
                    '$requestIdProvider' => new Reference(RequestIdProviderInterface::class),
                ])
                ->addTag('twig.extension')
            ;
        }
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
