<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\DependencyInjection;

use SBSEDV\Bundle\RequestIdBundle\Generator\UuidRequestIdGenerator;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\FalseTrustStrategy;
use SBSEDV\Bundle\RequestIdBundle\TrustStrategy\TrueTrustStrategy;
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
                ->scalarNode('generator')
                    ->info('The service ID of the Request-ID generator.')
                    ->defaultValue(UuidRequestIdGenerator::class)
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
                    ->treatTrueLike(TrueTrustStrategy::class)
                    ->treatFalseLike(FalseTrustStrategy::class)
                    ->treatNullLike(FalseTrustStrategy::class)
                    ->defaultValue(FalseTrustStrategy::class)
                    ->cannotBeEmpty()
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

                ->arrayNode('http_client')
                    ->treatFalseLike(['enabled' => false])
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->arrayNode('header_names')
                            ->info('The names of the http-headers that should be logged.')
                            ->scalarPrototype()->end()
                            ->defaultValue(['x-request-id'])
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
