<?php

namespace Codeages\Plumber;

use Codeages\Plumber\Queue\Job;
use Codeages\Plumber\Queue\QueueFactory;
use Codeages\RateLimiter\RateLimiter;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Swoole\Process;

class Plumber
{
    const ALREADY_RUNNING_ERROR = 1;

    const LOCK_PROCESS_ERROR = 2;

    const WORKER_STATUS_IDLE = 'idle';

    const WORKER_STATUS_BUSY = 'busy';

    const WORKER_STATUS_LIMITED = 'limited';

    const WORKER_STATUS_FAILED = 'failed';

    private $op;

    private $bootstrapFile;

    private $options;

    /**
     * @var ContainerInterface
     */
    private $container;

    private $pidFile;

    private $queueFactory;

    private $limiterFactory;

    private $logger;

    private $reserveTimes = 1;

    /**
     * Plumber constructor.
     *
     * @param string $op
     * @param string $bootstrapFile
     * @throws PlumberException
     */
    public function __construct($op, $bootstrapFile)
    {
        $this->op = $op;
        $this->bootstrapFile = $bootstrapFile;

        $bootstrap = require $bootstrapFile;

        $this->options = OptionsResolver::resolve($bootstrap['options']);
        $this->container = $bootstrap['container'];

        $this->logger = $logger = $this->createLogger();
        ErrorHandler::register($logger);

        $this->pidFile = new PidFile($this->options['pid_path']);
        $this->queueFactory = new QueueFactory($this->options['queues'], $logger);
        $this->limiterFactory = !empty($this->options['rate_limits']) ? new RateLimiterFactory($this->options['rate_limits']) : null;
    }

    /**
     * @throws \Exception
     */
    public function main()
    {
        switch ($this->op) {
            case 'run':
                $this->start(false);
                break;
            case 'start':
                $this->start(true);
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->restart();
                break;
        }
    }

    /**
     * @param bool $daemon
     *
     * @throws \Exception
     */
    private function start($daemon = false)
    {
        if ($this->pidFile->isRunning()) {
            echo "error: plumber is already running(PID: {$this->pidFile->read()}).\n";
            exit(self::ALREADY_RUNNING_ERROR);
        }

        if ($daemon) {
            Process::daemon(true, false);
        }

        if (!$this->pidFile->write(posix_getpid())) {
            echo 'error: lock process error.';
            exit(self::LOCK_PROCESS_ERROR);
        }

        $logger = $this->logger;

        $plumberOptions = $this->options;
        $workersOptions = $this->getWorkersOptions();
        $recreateLimiter = $this->createWorkerRecreateLimiter();
        $workerNum = count($workersOptions);

        $this->setMasterProcessName($workerNum);

        $pool = new Process\Pool($workerNum);
        $pool->on('WorkerStart', function (Process\Pool $pool, $workerId) use ($plumberOptions, $workersOptions, $logger, $recreateLimiter) {
            $running = true;
            pcntl_signal(SIGTERM, function () use (&$running) {
                $running = false;
            });
            pcntl_signal(SIGINT, function () use (&$running) {
                $running = false;
            });

            $options = $workersOptions[$workerId];

            $remainTimes = $recreateLimiter->getAllow($workerId);
            if ($remainTimes <= 0) {
                $logger->error("worker #{$workerId}, topic {$options['topic']}: worker restart failed.");
                $this->setWorkerProcessName($pool, $workerId, $options['topic'], self::WORKER_STATUS_FAILED);

                while (true) {
                    pcntl_signal_dispatch();
                    $logger->error("worker #{$workerId}, topic {$options['topic']}: worker is failed.");
                    sleep(2);
                }
            }

            if ($remainTimes < $recreateLimiter->getMaxAllowance()) {
                $logger->notice("worker #{$workerId}, topic {$options['topic']}: worker is restarting, remain {$remainTimes} times.");
                sleep(1);
            }

            $recreateLimiter->check($workerId);

            $this->setWorkerProcessName($pool, $workerId, $options['topic'], self::WORKER_STATUS_IDLE);

            /** @var WorkerInterface $worker */
            $worker = new $options['class']();

            if ($worker instanceof LoggerAwareInterface) {
                $worker->setLogger($logger);
            }

            if ($worker instanceof  ProcessAwareInterface) {
                $worker->setProcess($pool->getProcess());
            }

            if ($worker instanceof  ContainerAwareInterface && $this->container) {
                $worker->setContainer($this->container);
            }

            $queue = $this->queueFactory->create($options['queue']);
            $topic = $queue->listenTopic($options['topic']);

            $consumeLimiter = !empty($options['rate_limit']) && !empty($this->limiterFactory) ? $this->limiterFactory->create($options['rate_limit']) : null;
            if ($consumeLimiter) {
                $consumeLimiter->purge('consume');
                $logger->info("worker #{$workerId}, topic {$options['topic']}: rate limiter created.");
            }

            if (!empty($options['hour_limit']) && !empty($plumberOptions['hour_limits'][$options['hour_limit']])) {
                $hourLimiterOptions = $plumberOptions['hour_limits'][$options['hour_limit']];
                $hourLimiter = new HourLimiter($hourLimiterOptions['start'], $hourLimiterOptions['end']);
                $logger->info("worker #{$workerId}, topic {$options['topic']}: hour limiter created.");
            } else {
                $hourLimiter = null;
            }

            $logger->info("worker #{$workerId}, topic {$options['topic']}: worker started.");

            while ($running) {
                if ($consumeLimiter) {
                    $remainTimes = $consumeLimiter->getAllow('consume');
                    if ($remainTimes <= 0) {
                        $logger->info("worker #{$workerId}, topic {$options['topic']}: rate limiter limited.");
                        $this->setWorkerProcessName($pool, $workerId, $options['topic'], self::WORKER_STATUS_LIMITED);
                        pcntl_signal_dispatch();
                        sleep(2);
                        continue;
                    }
                }

                if ($hourLimiter && $hourLimiter->isLimited()) {
                    $logger->info("worker #{$workerId}, topic {$options['topic']}: hour limiter limited.");
                    pcntl_signal_dispatch();
                    sleep(60);
                    continue;
                }

                if (0 === $this->reserveTimes % 100) {
                    $logger->info("worker #{$workerId}, topic {$options['topic']}: reserving {$this->reserveTimes} times.");
                }

                $job = $topic->reserveJob(true);

                if ($job) {
                    $logger->info("worker #{$workerId}, topic {$options['topic']}: reserved job #{$job->getId()}.", $job->toArray());
                }

                ++$this->reserveTimes;

                pcntl_signal_dispatch();
                if (is_null($job)) {
                    continue;
                }

                if ($consumeLimiter) {
                    $consumeLimiter->check('consume');
                }

                //@see https://github.com/swoole/swoole-src/issues/183
                try {
                    $this->setWorkerProcessName($pool, $workerId, $options['topic'], self::WORKER_STATUS_BUSY);
                    $code = $worker->execute($job);
                    switch ($code) {
                        case WorkerInterface::FINISH:
                            $topic->finishJob($job);
                            $logger->info("worker #{$workerId}, topic {$options['topic']}: finish job #{$job->getId()}.");
                            break;
                        case WorkerInterface::BURY:
                            $topic->buryJob($job);
                            $logger->notice("worker #{$workerId}, topic {$options['topic']}: bury job #{$job->getId()}.");
                            break;
                        case WorkerInterface::RETRY:
                            $topic->finishJob($job);

                            $retryJob = new Job();
                            $retryJob->setId($job->getId());
                            $retryJob->setDelay($job->getDelay());
                            $retryJob->setPriority($job->getPriority());
                            $retryJob->setTtr($job->getTtr());
                            $retryJob->setBody($job->getBody());

                            $topic->putJob($retryJob);
                            $logger->info("worker #{$workerId}, topic {$options['topic']}: retry job #{$job->getId()}, new job #{$retryJob->getId()}.");
                            break;
                        default:
                            throw new PlumberException('Worker execute must return code.');
                    }

                    $this->setWorkerProcessName($pool, $workerId, $options['topic'], self::WORKER_STATUS_IDLE);
                } catch (\Throwable $e) {
                    $logger->error($e);
                    throw $e;
                }
            }
        });

        $pool->on('WorkerStop', function (Process\Pool $pool, $workerId) use ($daemon, $logger) {
            if ($daemon) {
                $logger->info("worker #{$workerId} is stopped.");
            }
        });

        $pool->start();
    }

    /**
     * Stop worker processes.
     */
    private function stop()
    {
        $pid = $this->pidFile->read();
        if (!$pid) {
            echo "plumber is not running.\n";

            return;
        }

        echo 'plumber is stopping...';
        posix_kill($pid, SIGTERM);
        while (1) {
            if (Process::kill($pid, 0)) {
                continue;
            }

            $this->pidFile->destroy();

            echo "[OK]\n";
            break;
        }
    }

    /**
     * @throws \Exception
     */
    private function restart()
    {
        $this->stop();

        sleep(1);

        echo 'plumber is starting...[OK]';
        $this->start(true);
    }

    /**
     * @param $op
     *
     * @return Logger
     *
     * @throws \Exception
     */
    private function createLogger()
    {
        $name = isset($this->options['app_name']) ? "{$this->options['app_name']}.plumber" : 'plumber';

        $logger = new Logger($name);
        if ('run' == $this->op) {
            $logger->pushHandler(new StreamHandler('php://output'));
        } else {
            $logger->pushHandler(new StreamHandler($this->options['log_path']));
        }

        return $logger;
    }

    private function getWorkersOptions()
    {
        $options = [];
        $workerId = 0;
        foreach ($this->options['workers'] as $workerOptions) {
            $num = isset($workerOptions['num']) ? $workerOptions['num'] : 1;
            for ($i = 1; $i <= $num; ++$i) {
                $options[$workerId] = $workerOptions;
                ++$workerId;
            }
        }

        return $options;
    }

    private function createWorkerRecreateLimiter()
    {
        $name = sprintf(
            'plumber:%s:rate_limiter:worker_recreate',
            isset($this->options['app_name']) ? $this->options['app_name'] : ''
        );

        return new RateLimiter(
            $name,
            10,
            60,
            new SwooleTableRateLimiterStorage()
        );
    }

    private function setWorkerProcessName(Process\Pool $pool, $workerId, $topic, $status)
    {
        $name = sprintf(
            '%splumber: worker #%s listening %s topic [%s]',
            isset($this->options['app_name']) ? "{$this->options['app_name']}." : '',
            $workerId,
            $topic,
            $status
        );

        @$pool->getProcess()->name($name);
    }

    private function setMasterProcessName($workerNum)
    {
        $name = sprintf(
            '%splumber: master [workers: %d, bootstrap: %s]',
            isset($this->options['app_name']) ? "{$this->options['app_name']}." : '',
            $workerNum,
            $this->bootstrapFile
        );

        @swoole_set_process_name($name);
    }
}
