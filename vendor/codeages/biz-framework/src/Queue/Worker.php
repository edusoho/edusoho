<?php

namespace Codeages\Biz\Framework\Queue;

use Codeages\Biz\Framework\Queue\Driver\Queue;
use Symfony\Component\Lock\LockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;

class Worker
{
    const EXIT_CODE_MEMORY_EXCEEDED = 1;

    const EXIT_CODE_TIMEOUT = 2;

    const EXIT_CODE_EXCEPTION = 3;

    const EXIT_CODE_RUNNING = 4;

    protected $queue;

    protected $options;

    protected $shouldQuit = false;

    protected $failer;

    protected $lock;

    protected $logger;

    public function __construct(Queue $queue, JobFailer $failer, LockInterface $lock, LoggerInterface $logger, array $options = array())
    {
        $this->queue = $queue;
        $this->failer = $failer;
        $this->lock = $lock;
        $this->logger = $logger;
        $this->options = array_merge(array(
            'memory_limit' => 256,
            'sleep' => 2,
            'tries' => 0,
            'once' => false,
            'stop_when_idle' => false,
        ), $options);
    }

    public function run()
    {
        try {
            $acquired = $this->lock->acquire();
        } catch (LockConflictedException $e) {
            $this->logger->error($this->createMessage("Acquire lock error: {$e->getMessage()}"));
        } catch (LockAcquiringException $e) {
            $this->logger->error($this->createMessage("Acquire lock error: {$e->getMessage()}"));
        }

        if (!$acquired) {
            $this->logger->warning($this->createMessage('Acquire lock failed, because other process is running.'));
            $this->stop(self::EXIT_CODE_RUNNING);
        }

        while (true) {
            $job = $this->runNextJob();
            if (empty($job)) {
                sleep($this->options['sleep']);
            }
            $this->stopIfNecessary($job);
        }

        $this->lock->release();
    }

    public function runNextJob()
    {
        $job = $this->getNextJob();
        if ($job) {
            $this->logger->info($this->createMessage("Start execute job #{$job->getId()}."));
            $this->executeJob($job);
            $this->logger->info($this->createMessage("End execute job #{$job->getId()}."));

            return $job;
        } else {
            $this->logger->info($this->createMessage('No job.'));
        }
    }

    protected function getNextJob()
    {
        try {
            return $job = $this->queue->pop();
        } catch (\Exception $e) {
            $this->logger->error($this->createMessage("Pop job #{$job->getId()} error: {$e->getMessage()}"));
            $this->shouldQuit = true;
        } catch (\Throwable $e) {
            $this->logger->error($this->createMessage("Pop job #{$job->getId()} error: {$e->getMessage()}"));
            $this->shouldQuit = true;
        }
    }

    protected function executeJob($job)
    {
        $this->registerTimeoutHandler($job);

        try {
            $result = $job->execute();
        } catch (\Exception $e) {
            $this->logger->error($this->createMessage("Execute job #{$job->getId()} error: {$e->getMessage()}"));
            $this->shouldQuit = true;
        } catch (\Throwable $e) {
            $this->logger->error($this->createMessage("Execute job #{$job->getId()} error: {$e->getMessage()}"));
            $this->shouldQuit = true;
        }

        if (is_array($result)) {
            $result = array_values($result);
            $code = isset($result[0]) ? $result[0] : null;
            $message = isset($result[1]) ? $result[1] : '';
        } else {
            $code = $result;
            $message = '';
        }

        if (empty($code) || Job::FINISHED === $code) {
            $this->queue->delete($job);

            return;
        }

        if (Job::FAILED_RETRY == $code) {
            $executions = $job->getMetadata('executions', 1);
            if ($executions - 1 < $this->options['tries']) {
                $this->queue->release($job);
                $this->logger->warning($this->createMessage("Execute job #{$job->getId()} failed, retry {$executions} times."));

                return;
            }
        }

        $this->failer->log($job, $this->queue->getName(), $message);
        $this->queue->delete($job);

        $this->logger->warning($this->createMessage("Execute job #{$job->getId()} failed, drop it."));
    }

    protected function registerTimeoutHandler($job)
    {
        $timeout = $job->getMetadata('timeout', 0);
        if (empty($timeout)) {
            return;
        }

        if ($this->isSupportAsyncSignal()) {
            pcntl_async_signals(true);
            pcntl_signal(SIGALRM, function () {
                $this->kill(self::EXIT_CODE_TIMEOUT);
            });

            pcntl_alarm($timeout);
        }
    }

    protected function isSupportAsyncSignal()
    {
        return version_compare(PHP_VERSION, '7.1.0') >= 0 &&
               extension_loaded('pcntl');
    }

    public function kill($status = 0)
    {
        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }

        $this->stop($status);
    }

    protected function stop($status = 0)
    {
        if ($status > 0) {
            $this->logger->warning($this->createMessage("Worker stopped. (exit code: {$status})"));
        } else {
            $this->logger->info($this->createMessage('Wroker stopped.'));
        }
        $this->lock->release();
        exit($status);
    }

    protected function stopIfNecessary($job)
    {
        if ($this->shouldQuit) {
            $this->stop(self::EXIT_CODE_EXCEPTION);
        }

        if (empty($job) && $this->options['stop_when_idle']) {
            $this->stop();
        }

        if (true == $this->options['once']) {
            $this->stop();
        }

        if ($this->isMemoryExceeded($this->options['memory_limit'])) {
            $this->stop(self::EXIT_CODE_MEMORY_EXCEEDED);
        }
    }

    protected function isMemoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    protected function createMessage($message)
    {
        return sprintf('[Queue Worker - %s] %s', $this->queue->getName(), $message);
    }
}
