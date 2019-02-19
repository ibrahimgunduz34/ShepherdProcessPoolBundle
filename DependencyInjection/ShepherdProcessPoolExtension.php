<?php
namespace Shepherd\Bundle\ProcessPoolBundle\DependencyInjection;

use Shepherd\Bundle\ProcessPoolBundle\ProcessPool;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class ShepherdProcessPoolExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $defaults = $config['defaults'];

        foreach ((array) $config['pools'] as $name => $options) {
            $pool = new Definition(ProcessPool::class);
            $pool->setArguments([
                new Reference('logger'),
                is_null($options['max_processes']) ? $defaults['max_processes'] : $options['max_processes'],
                is_null($options['fail_on_error']) ? $defaults['fail_on_error'] : $options['fail_on_error']
            ]);
            $container->setDefinition(sprintf('shepherd.pool.%s', $name), $pool);
        }
    }
}
