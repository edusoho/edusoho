<?php

namespace MarketingMallBundle\Biz\SyncList\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use MarketingMallBundle\Biz\SyncList\Dao\SyncListDao;
use MarketingMallBundle\Biz\SyncList\Service\SyncListService;

class SyncListServiceImpl extends BaseService implements SyncListService
{

    public function addSyncList($syncList)
    {
        if (!ArrayToolkit::requireds($syncList, ['type', 'data'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $syncList = ArrayToolkit::parts($syncList, ['type', 'data']);

        return $this->getSyncListDao()->create($syncList);
    }

    public function getSyncType()
    {
        return $this->getSyncListDao()->getSyncType();
    }

    public function getSyncIds()
    {
        return $this->getSyncListDao()->getSyncIds();
    }

    public function getSyncDataId($id)
    {
        return $this->getSyncListDao()->getSyncDataId($id);
    }


    public function syncStatusUpdate($ids)
    {
        return $this->getSyncListDao()->syncStatusUpdate($ids);
    }

    public function getSyncList($cursorAddress, $cursorType)
    {
        return $this->getSyncListDao()->findSyncListByCursor($cursorAddress, $cursorType);
    }
    
    /**
     * @return SyncListDao
     */
    protected function getSyncListDao()
    {
        return $this->createDao('MarketingMallBundle:SyncList:SyncListDao');
    }
}