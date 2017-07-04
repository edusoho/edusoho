<?php

namespace Biz\Crontab\Service\Impl;

use Biz\Crontab\Service\CrontabService;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class CrontabServiceImpl implements CrontabService
{
    public function setNextExcutedTime($time)
    {
        if ($time >= $this->getNextExcutedTime()) {
            return;
        }

        $filePath = $this->getCrontabConfigYml();
        $yaml = new Yaml();
        $content = $yaml->dump(array('crontab_next_executed_time' => $time));
        $fh = fopen($filePath, 'w');
        fwrite($fh, $content);
        fclose($fh);
    }

    public function getNextExcutedTime()
    {
        $filePath = $this->getCrontabConfigYml();
        $yaml = new Yaml();

        if (!file_exists($filePath)) {
            $content = $yaml->dump(array('crontab_next_executed_time' => 0));
            $fh = fopen($filePath, 'w');
            fwrite($fh, $content);
            fclose($fh);
        }

        $fileContent = file_get_contents($filePath);
        $config = $yaml->parse($fileContent);

        return $config['crontab_next_executed_time'];
    }

    private function getCrontabConfigYml()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/data/crontab_config.yml';
    }
}
