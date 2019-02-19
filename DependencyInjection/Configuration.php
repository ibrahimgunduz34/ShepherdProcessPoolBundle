<?php
namespace Shepherd\Bundle\ProcessPoolBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('shepherd');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('shepherd');
        }

        $rootNode
            ->children()
                ->arrayNode("defaults")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode("max_processes")->defaultValue(4)->end()
                        ->booleanNode("fail_on_error")->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode("pools")
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode("max_processes")->defaultValue(null)->end()
                            ->booleanNode("fail_on_error")->defaultValue(null)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
