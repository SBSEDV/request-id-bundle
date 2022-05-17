<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\DependencyInjection;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProvider;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\TrustedIncomingRequestIdStrategy;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\UntrustedIncomingRequestIdStrategy;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sbsedv_request_id');

        $treeBuilder->getRootNode() // @phpstan-ignore-line
            ->children()
                ->scalarNode('provider')
                    ->info('The service ID of the RequestId provider.')
                    ->defaultValue('sbsedv_request_id.provider.default')
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('outgoing_http_header')
                    ->info('The HTTP-Header name which will be added to every response with the RequestId as value.')
                    ->treatFalseLike(null)
                    ->defaultValue('x-request-id')
                ->end()

                ->scalarNode('incoming_http_header')
                    ->info('The incoming HTTP-Header name that contains the RequestId to use.')
                    ->defaultValue('x-request-id')
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('trust_incoming_http_header')
                    ->info('The strategy used to determine whether to trust the incoming HTTP-Header.')
                    ->treatTrueLike(TrustedIncomingRequestIdStrategy::class)
                    ->treatFalseLike(UntrustedIncomingRequestIdStrategy::class)
                    ->treatNullLike(UntrustedIncomingRequestIdStrategy::class)
                    ->defaultValue(UntrustedIncomingRequestIdStrategy::class)
                    ->cannotBeEmpty()
                ->end()

                ->arrayNode('default_provider')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('id_length')->defaultValue(RequestIdProvider::DEFAULT_LENGTH)->end()
                    ->end()
                ->end()

                ->booleanNode('twig_error_template')->defaultTrue()->end()
                ->scalarNode('twig_function_name')
                    ->info('Name of the registered twig function.')
                    ->defaultValue('request_id')
                    ->cannotBeEmpty()
                ->end()

                ->arrayNode('monolog_processor')
                    ->treatFalseLike(['enabled' => false])
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('key')
                            ->info('Array key to which the request id will be set.')
                            ->defaultValue('request_id')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
