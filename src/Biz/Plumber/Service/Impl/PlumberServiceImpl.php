<?php

namespace Biz\Plumber\Service\Impl;

use Biz\BaseService;
use Biz\Plumber\Service\PlumberService;
use Topxia\Service\Common\ServiceKernel;

class PlumberServiceImpl extends BaseService implements PlumberService
{
    public function canOperate()
    {
        $pid = $this->getProcessId();

        if (empty($pid)) {
            return true;
        }

        exec("ps aux -l|grep {$pid}|grep -v grep", $activeProcess);

        if (empty($activeProcess)) {
            return true;
        }

        $currentExecUser = getenv('USER');
        exec("ps aux -l|grep {$pid} |grep {$currentExecUser} |grep -v grep", $process);

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

        exec("ps -lg {$pid}", $output);

        return [
            count($output) > 1 ? self::STATUS_EXECUTING : self::STATUS_STOPPED,
            $output,
        ];
    }

    public function start()
    {
        return $this->execPlumberCmd('bin/plumber start -b bootstrap/bootstrap_plumber.php');
    }

    public function restart()
    {
        return $this->execPlumberCmd('bin/plumber restart -b bootstrap/bootstrap_plumber.php');
    }

    public function stop()
    {
        return $this->execPlumberCmd('bin/plumber stop -b bootstrap/bootstrap_plumber.php');
    }

    protected function execPlumberCmd($cmd)
    {
        $oldPid = $this->getProcessId();

        $rootDir = $this->biz['kernel.root_dir'].'/../';

        $php = empty(ServiceKernel::instance()->getParameter('php_bash')) ? 'php' : ServiceKernel::instance()->getParameter('php_bash');

        $process = popen("cd {$rootDir} && {$php} {$cmd} &", 'r');

        sleep(5);

        $logger = $this->biz['plumber.logger'];
        $logger->info(json_encode([$cmd, $this->getProcessId()]));

        pclose($process);

        return $oldPid != $this->getProcessId();
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
