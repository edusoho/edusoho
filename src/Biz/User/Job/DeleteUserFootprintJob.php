<?php

namespace Biz\User\Job;

use Biz\User\Service\UserFootprintService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DeleteUserFootprintJob extends AbstractJob
{
    public function execute()
    {
        $this->getUserFootprintService()->deleteUserFootprintsBeforeDate(date('Y-m-d', strtotime('-2 year', time())));
    }

    /**
     * @return UserFootprintService
     */
    protected function getUserFootprintService()
    {
        return $this->biz->service('User:UserFootprintService');
    }
}
