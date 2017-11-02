<?php

namespace Biz\Xapi\Job;

use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class AddActivityWatchToStatementJob extends AbstractJob
{
    public function execute()
    {
        $conditions = array(
            'is_push' => 0,
            'created_time_LT' => time() - 60*60
        );

        $orderBy = array('created_time' => 'ASC');

        $watchLogs = $this->getXapiService()->searchWatchLogs($conditions, $orderBy, 0, 10);

        foreach ($watchLogs as $watchLog) {
            $statement = array(

            );
        }


    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->biz->service('Xapi:XapiService');
    }
}