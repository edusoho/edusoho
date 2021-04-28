<?php

namespace Biz\WeChat\Service\Impl;

use Biz\BaseService;
use Biz\WeChat\Dao\SubscribeRecordDao;
use Biz\WeChat\Service\SubscribeRecordService;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use ESCloud\SDK\Service\NotificationService;

class SubscribeRecordServiceImpl extends BaseService implements SubscribeRecordService
{
    public function synchronizeSubscriptionRecords()
    {
        $options = [
            'createdTime' => $this->getLastCreatedTime(),
        ];

        $SynchronizeRecords = $this->getSDKNotificationService()->searchRecords($options);

        $batchUpdateHelper = new BatchCreateHelper($this->getSubscribeRecordDao());

        foreach ($SynchronizeRecords as $record) {
            $createRecord = [
                'toId' => $record['toId'],
                'templateCode' => $record['templateCode'],
                'templateType' => $record['templateType'],
                'createdTime' => $record['createdTime'],
                'updatedTime' => time(),
            ];
            $batchUpdateHelper->add($createRecord);
        }
        $batchUpdateHelper->flush();

    }


    public function getLastCreatedTime()
    {
        $lastRecord =  $this->getSubscribeRecordDao()->getLastRecord();

        return $lastRecord ? $lastRecord['createdTime'] : 0;
    }

    /**
     * @return SubscribeRecordDao
     */
    protected function getSubscribeRecordDao()
    {
        return $this->createDao('WeChat:SubscribeRecord');
    }

    /**
     * @return NotificationService
     */
    protected function getSDKNotificationService()
    {
        return $this->biz['ESCloudSdk.notification'];
    }
}
