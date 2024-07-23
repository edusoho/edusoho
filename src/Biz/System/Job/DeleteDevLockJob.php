<?php

namespace Biz\System\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Symfony\Component\Filesystem\Filesystem;

class DeleteDevLockJob extends AbstractJob
{
    public function execute()
    {
        $rootPath = __DIR__.'/../../../../';
        $filepath = $rootPath.'app/data/dev.lock';
        $filesystem = new Filesystem();
        if ($filesystem->exists($filepath)) {
            $filesystem->remove($filepath);
        }
    }
}