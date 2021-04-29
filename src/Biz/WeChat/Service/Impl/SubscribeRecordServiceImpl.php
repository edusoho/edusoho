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
            'createdTime_GT' => $this->getLastCreatedTime(),
        ];

        $synchronizeRecords = $this->getSDKNotificationService()->searchRecords($options);

        if (empty($synchronizeRecords['data'])) {
            return;
        }

        $batchUpdateHelper = new BatchCreateHelper($this->getSubscribeRecordDao());
        foreach ($synchronizeRecords['data'] as $record) {
            $createRecord = [
                'toId' => $record['to_id'],
                'templateCode' => $record['template_code'],
                'templateType' => 'once',
                'createdTime' => strtotime($record['created_time']),
            ];
            $batchUpdateHelper->add($createRecord);
        }
        $batchUpdateHelper->flush();
    }

    public function getLastCreatedTime()
    {
        $lastRecord = $this->getSubscribeRecordDao()->getLastRecord();

        return $lastRecord ? $lastRecord['createdTime'] : 0;
    }

    /**
     * @return SubscribeRecordDao
     */
    protected function getSubscribeRecordDao()
    {
        return $this->createDao('WeChat:SubscribeRecordDao');
    }

    /**
     * @return NotificationService
     */
    protected function getSDKNotificationService()
    {
        return $this->biz['ESCloudSdk.notification'];
    }
}
