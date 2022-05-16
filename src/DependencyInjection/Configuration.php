<?php declare(strict_types=1);

namespace SBSEDV\Bundle\RequestIdBundle\DependencyInjection;

use SBSEDV\Bundle\RequestIdBundle\Provider\RequestIdProvider;
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

                ->scalarNode('http_header')
                    ->treatFalseLike(null)
                    ->info('The HTTP-Header name which will be added to every response with the RequestId as value.')
                    ->defaultValue('x-request-id')
                ->end()

                ->scalarNode('prefix')
                    ->defaultValue('')
                    ->info('Prefix that all built-in providers will use for id generation.')
                ->end()

                ->arrayNode('default_provider')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('id_length')->defaultValue(RequestIdProvider::DEFAULT_LENGTH)->end()
                    ->end()
                ->end()

                ->booleanNode('twig_error_template')->defaultTrue()->end()

                ->arrayNode('monolog_processor')
                    ->treatFalseLike(['enabled' => false])
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('key')
                            ->info('Array key to which the request id will be set.')
                            ->defaultValue('uid')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
