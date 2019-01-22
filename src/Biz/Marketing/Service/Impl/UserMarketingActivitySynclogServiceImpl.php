<?php

namespace Biz\Marketing\Service\Impl;

use Biz\Marketing\Service\UserMarketingActivitySynclogService;
use Biz\BaseService;
use Biz\Marketing\Dao\UserMarketingActivitySynclogDao;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;

class UserMarketingActivitySynclogServiceImpl extends BaseService implements UserMarketingActivitySynclogService
{
    public function createSyncLog($syncLog)
    {
        if (!ArrayToolkit::requireds($syncLog, array('args', 'data', 'target', 'rangeEndTime'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $syncLog = ArrayToolkit::parts(
            $syncLog,
            array(
                'args',
                'data',
                'target',
                'targetValue',
                'rangeStartTime',
                'rangeEndTime',
            )
        );

        return $this->getUserMarketingActivitySynclogDao()->create($syncLog);
    }

    public function getLastSyncLogByTargetAndTargetValue($target, $targetValue)
    {
        return $this->getUserMarketingActivitySynclogDao()->getLastSyncLogByTargetAndTargetValue($target, $targetValue);
    }

    /**
     * @return UserMarketingActivitySynclogDao
     */
    protected function getUserMarketingActivitySynclogDao()
    {
        return $this->createDao('Marketing:UserMarketingActivitySynclogDao');
    }
}
