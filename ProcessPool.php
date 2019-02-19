<?php
namespace Shepherd\Bundle\ProcessPoolBundle;

use Shepherd\Bundle\ProcessPoolBundle\Exception\ProcessExecutionError;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class ProcessPool
{
    /** @var  LoggerInterface */
    private $logger;

    /** @var array */
    private $pool = [];

    /** @var int */
    private $maxProcesses;

    /** @var  Boolean */
    private $failOnError;

    /**
     * ProcessPool constructor.
     * @param LoggerInterface $logger
     * @param int $maxProcesses
     * @param bool $failOnError
     */
    public function __construct(LoggerInterface $logger, $maxProcesses, $failOnError = false)
    {
        $this->logger = $logger;
        $this->maxProcesses = $maxProcesses;
        $this->failOnError = $failOnError;
    }

    /**
     * @param Process $process
     */
    public function append(Process $process)
    {
        $this->pool[] = $process;
    }

    /**
     * @return int
     */
    private function getActiveProcessCount()
    {
        return count(array_filter($this->pool, function ($process) {
            /** @var Process $process */
            return $process->isRunning();
        }));
    }

    public function start()
    {
        $this->logger->debug('Starting queue consumption.');

        while (count($this->pool) > 0) {
            /** @var Process $process */
            foreach ($this->pool as $key => $process) {
                if ($process->isRunning()) {
                    continue;
                }

                if ($process->isTerminated()) {
                    if (!$process->isSuccessful()) {
                        $this->logger->error('sub process failed.', array(
                            'command' => $process->getCommandLine(),
                            'output' => $process->getOutput()
                        ));
                        if ($this->failOnError) {
                            throw new ProcessExecutionError("The process returned non-zero exit code.");
                        }
                    }
                    unset($this->pool[$key]);
                    continue;
                }

                if ($this->getActiveProcessCount() < $this->maxProcesses) {
                    $process->start();
                }
            }
        }

        $this->logger->debug('All processes are done.');
    }
}
