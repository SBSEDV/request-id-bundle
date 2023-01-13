<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle;

use SBSEDV\Bundle\RequestIdBundle\EventListener\IncomingHttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\EventListener\OutgoingHttpHeaderEventListener;
use SBSEDV\Bundle\RequestIdBundle\Generator\RequestIdGeneratorInterface;
use SBSEDV\Bundle\RequestIdBundle\HttpClient\HttpClientRequestIdLogger;
use SBSEDV\Bundle\RequestIdBundle\Monolog\RequestIdLogProcessor;
use SBSEDV\Bundle\RequestIdBundle\Twig\Extension\RequestIdExtension;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SBSEDVRequestIdBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definitions.php');
    }

    /**
     * {@inheritdoc}
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $bundles = $builder->getParameter('kernel.bundles');

        $container->import('../config/services/request_id_provider.php');
        $container->import('../config/services/trust_strategies.php');

        if ($config['http_client']['enabled']) {
            $container->import('../config/services/http_client.php');
            $container->services()->get(HttpClientRequestIdLogger::class)->arg('$headerNames', $config['http_client']['header_names']);
        }

        if ($config['monolog_processor']['enabled'] && \in_array(MonologBundle::class, $bundles, true)) {
            $container->import('../config/services/monolog_processor.php');
            $container->services()->get(RequestIdLogProcessor::class)->arg('$key', $config['monolog_processor']['key']);
        }

        if (\in_array(TwigBundle::class, $bundles, true)) {
            $container->import('../config/services/twig_extension.php');
            $container->services()->get(RequestIdExtension::class)->arg('$functionName', $config['twig_function_name']);
        }

        $container->import('../config/services/request_id_generator.php');
        $container->services()->alias(RequestIdGeneratorInterface::class, $config['generator']);

        $container->import('../config/services/incoming_event_subscriber.php');
        $container->services()->get(IncomingHttpHeaderEventListener::class)
            ->arg('$headerName', $config['incoming_http_header'])
            ->arg('$trustStrategy', service($config['trust_incoming_http_header']))
        ;

        $outgoingHeaderName = $config['outgoing_http_header'] ?? null;
        if (\is_string($outgoingHeaderName)) {
            $container->import('../config/services/outgoing_event_subscriber.php');
            $container->services()->get(OutgoingHttpHeaderEventListener::class)
                ->arg('$headerName', $outgoingHeaderName)
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $bundles = $builder->getParameter('kernel.bundles');

        $loadTwigErrorTemplate = $builder->hasParameter('sbsedv_request_id.twig_error_template') ? $builder->getParameter('sbsedv_request_id.twig_error_template') : true;

        if (\in_array(TwigBundle::class, $bundles, true) && $loadTwigErrorTemplate) {
            $container->extension('twig', [
                'paths' => [
                    __DIR__.'/../templates/bundles/TwigBundle' => 'Twig', // @phpstan-ignore-line
                ],
            ]);
        }
    }
}
