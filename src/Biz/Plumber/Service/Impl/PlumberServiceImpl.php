<?php

namespace Biz\Plumber\Service\Impl;

use Biz\BaseService;
use Biz\Plumber\Service\PlumberService;

class PlumberServiceImpl extends BaseService implements PlumberService
{
    public function canOperate()
    {
        $pid = $this->getProcessId();

        if (empty($pid)) {
            return true;
        }

        exec("ps au -l|grep {$pid}", $activeProcess);

        if (empty($activeProcess)) {
            return true;
        }

        $currentExecUser = getenv('USER');
        exec("ps au -l|grep {$pid} |grep {$currentExecUser}", $process);

        return !empty($process);
    }

    /**
     * @return array result with status, shellOutput
     */
    public function getPlumberStatus()
    {
        $pid = $this->getProcessId();

        if (empty($pid)) {
            return [self::STATUS_STOPPED, []];
        }

        exec("ps au -l|head -1; ps au -l|grep -a {$pid}", $output);

        return [
            count($output) > 1 ? self::STATUS_EXECUTING : self::STATUS_STOPPED,
            $output,
        ];
    }

    public function start()
    {
        $result = $this->execPlumberCmd('bin/plumber run -b bootstrap/bootstrap_plumber.php');

        file_put_contents("{$this->biz['kernel.root_dir']}/logs/test.log", 'start result'.json_encode($result).PHP_EOL, FILE_APPEND);

        return $this->getPlumberStatus();
    }

    public function restart()
    {
        $result = $this->execPlumberCmd('bin/plumber restart -b bootstrap/bootstrap_plumber.php');

        file_put_contents("{$this->biz['kernel.root_dir']}/logs/test.log", 'restart result'.json_encode($result).PHP_EOL, FILE_APPEND);

        return $this->getPlumberStatus();
    }

    public function stop()
    {
        $result = $this->execPlumberCmd('bin/plumber stop -b bootstrap/bootstrap_plumber.php');

        file_put_contents("{$this->biz['kernel.root_dir']}/logs/test.log", 'result'.json_encode($result).PHP_EOL, FILE_APPEND);

        return $this->getPlumberStatus();
    }

    protected function execPlumberCmd($cmd)
    {
        $rootDir = $this->biz['kernel.root_dir'].'/../';

        exec("cd {$rootDir} && $cmd", $result);

        return $result;
    }

    protected function getProcessId()
    {
        $path = $this->biz['kernel.root_dir'].'/data/plumber.pid';

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return null;
    }
}
