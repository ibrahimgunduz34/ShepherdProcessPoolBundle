<?php
namespace Shepherd\Bundle\ProcessPoolBundle\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shepherd\Bundle\ProcessPoolBundle\ProcessPool;
use Symfony\Component\Process\Process;

class ProcessPoolTest extends TestCase
{
    public function testAdd() {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $pool = new ProcessPool($loggerMock, 4, false);
        $pool->append(new Process(['ls', '-la']));
        $pool->append(new Process(['ls', '-la']));
        $pool->append(new Process(['ls', '-la']));
        $pool->append(new Process(['ls', '-la']));

        $reflection = new \ReflectionObject($pool);
        $prop = $reflection->getProperty('pool');
        $prop->setAccessible(true);
        $this->assertEquals(4, count($prop->getValue($pool)));
        $this->assertContainsOnlyInstancesOf(Process::class, $prop->getValue($pool));
    }

    public function testStart() {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $pool = new ProcessPool($loggerMock, 2, false);

        $pool->append(new Process(['sleep', '3']));
        $pool->append(new Process(['sleep', '3']));
        $pool->append(new Process(['sleep', '3']));
        $pool->append(new Process(['sleep', '3']));

        $reflection = new \ReflectionObject($pool);
        $prop = $reflection->getProperty('pool');
        $prop->setAccessible(true);

        $this->assertEquals(4, count($prop->getValue($pool)));

        /** @var Process $process */
        foreach ($prop->getValue($pool) as $process) {
            $this->assertEquals(Process::STATUS_READY, $process->getStatus());
        }

        $pool->start();

        $this->assertEquals(0, count($prop->getValue($pool)));
    }

    public function testStartAndSilentOnError()
    {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $pool = new ProcessPool($loggerMock, 2, false);

        $pool->append(new Process(['ls', '-la']));
        $pool->append(new Process(['dummy', 'command']));
        $pool->append(new Process(['ls', '-la']));

        $reflection = new \ReflectionObject($pool);
        $prop = $reflection->getProperty('pool');
        $prop->setAccessible(true);

        $this->assertEquals(3, count($prop->getValue($pool)));

        $pool->start();

        $this->assertEquals(0, count($prop->getValue($pool)));
    }

    /**
     * @expectedException \Shepherd\Bundle\ProcessPoolBundle\Exception\ProcessExecutionError
     */
    public function testStartAndFailOnError()
    {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $pool = new ProcessPool($loggerMock, 2, true);

        $pool->append(new Process(['ls', '-la']));
        $pool->append(new Process(['dummy', 'command']));
        $pool->append(new Process(['ls', '-la']));

        $pool->start();
    }
}
