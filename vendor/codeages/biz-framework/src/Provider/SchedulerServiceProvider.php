<?php

namespace Codeages\Biz\Framework\Provider;

use Codeages\Biz\Framework\Scheduler\Scheduler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SchedulerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['autoload.aliases']['Scheduler'] = 'Codeages\Biz\Framework\Scheduler';

        $biz['scheduler.options'] = array(
            'max_process_exec_time' => 600,
            'max_num' => 10,
            'timeout' => 120,
            'max_retry_num' => 5,
        );

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Scheduler\Command\TableCommand($biz);
        };

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Scheduler\Command\SchedulerCommand($biz);
        };
    }
}
