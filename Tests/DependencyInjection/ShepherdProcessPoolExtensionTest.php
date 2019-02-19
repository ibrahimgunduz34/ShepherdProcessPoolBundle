<?php
namespace Shepherd\Bundle\ProcessPoolBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shepherd\Bundle\ProcessPoolBundle\DependencyInjection\ShepherdProcessPoolExtension;
use Shepherd\Bundle\ProcessPoolBundle\ProcessPool;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ShepherdProcessPoolExtensionTest extends TestCase
{
    private function buildContainer(array $config) {
        $container = new ContainerBuilder();
        $extension = new ShepherdProcessPoolExtension();

        $loggerMock = $this->createMock(LoggerInterface::class);
        $container->set('logger', $loggerMock);
        $extension->load([$config], $container);
        return $container;
    }
    public function testPoolDefinitions()
    {

        $config = [
            'pools' => [
                'foo' => [
                    'max_processes' => 8,
                    'fail_on_error' => false
                ]
            ]
        ];
        $container = $this->buildContainer($config);

        $this->assertTrue($container->hasDefinition('shepherd.pool.foo'));

        $definition = $container->getDefinition('shepherd.pool.foo');

        $this->assertInstanceOf(ProcessPool::class, $container->get('shepherd.pool.foo'));
        $this->assertEquals($config['pools']['foo']['max_processes'], $definition->getArgument(1));
        $this->assertEquals($config['pools']['foo']['fail_on_error'], $definition->getArgument(2));
    }

    public function testDefaultValues()
    {

        $config = [
            'defaults' => [
                'max_processes' => 4,
                'fail_on_error' => true
            ],
            'pools' => [
                'foo' => [
                ]
            ]
        ];
        $container = $this->buildContainer($config);

        $this->assertTrue($container->hasDefinition('shepherd.pool.foo'));

        $definition = $container->getDefinition('shepherd.pool.foo');
        $this->assertInstanceOf(ProcessPool::class, $container->get('shepherd.pool.foo'));
        $this->assertEquals($config['defaults']['max_processes'], $definition->getArgument(1));
        $this->assertEquals($config['defaults']['fail_on_error'], $definition->getArgument(2));
    }

    public function testOverrideDefaultValues()
    {

        $config = [
            'defaults' => [
                'max_processes' => 4,
                'fail_on_error' => true
            ],
            'pools' => [
                'foo' => [
                    'max_processes' => 8
                ]
            ]
        ];
        $container = $this->buildContainer($config);

        $this->assertTrue($container->hasDefinition('shepherd.pool.foo'));

        $definition = $container->getDefinition('shepherd.pool.foo');
        $this->assertInstanceOf(ProcessPool::class, $container->get('shepherd.pool.foo'));
        $this->assertEquals($config['pools']['foo']['max_processes'], $definition->getArgument(1));
        $this->assertEquals($config['defaults']['fail_on_error'], $definition->getArgument(2));
    }
}
