<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Queue\Driver\SyncQueue;
use Codeages\Biz\Framework\Queue\JobFailer;
use Codeages\Biz\Framework\Queue\Command\WorkerCommand;
use Codeages\Biz\Framework\Queue\Command\TableCommand;

class QueueServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['autoload.aliases']['Queue'] = 'Codeages\Biz\Framework\Queue';
        $biz['console.commands'][] = function () use ($biz) {
            return new WorkerCommand($biz);
        };
        $biz['console.commands'][] = function () use ($biz) {
            return new TableCommand($biz);
        };

        $biz['queue.failer'] = function ($biz) {
            return new JobFailer($biz->dao('Queue:FailedJobDao'));
        };

        $biz['queue.connection.default'] = function ($biz) {
            return new SyncQueue('default', $biz, $biz['queue.failer']);
        };
    }
}
