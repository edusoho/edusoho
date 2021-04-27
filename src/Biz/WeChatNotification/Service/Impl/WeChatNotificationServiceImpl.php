<?php

namespace Biz\WeChatNotification\Service\Impl;

use Biz\BaseService;
use Biz\WeChatNotification\Service\WeChatNotificationService;
use Codeages\Biz\Framework\Dao\BatchCreateHelper;
use QiQiuYun\SDK\Service\WeChatService;

class WeChatNotificationServiceImpl extends BaseService implements WeChatNotificationService
{
    const SYNCHRONIZE_RECORD_PAGE = 10;

    public function synchronizeSubscriptionRecords()
    {
        $options = [
            'createdTime' => $this->getLastCreatedTime(),
            'page' => self::SYNCHRONIZE_RECORD_PAGE,
        ];

//        $SynchronizeRecords = $this->getSDKWeChatService()->getWechatSubscriptionRecords($options);

        $SynchronizeRecords = [
            1 => [
                'toId' => 133772,
                'templateCode' => 'dwuwaw#8y23',
                'templateType' => 'subscribe',
                'createdTime' => time(),
                'updatedTime' => time(),
            ],
        ];

        $batchUpdateHelper = new BatchCreateHelper($this->getWechatSubscribeRecordDao());

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
        $lastRecord = $this->getWechatSubscribeRecordDao()->getLastRecord();

        return $lastRecord ? $lastRecord['createdTime'] : '';
    }

    protected function getWechatSubscribeRecordDao()
    {
        return $this->createDao('WeChatNotification:WeChatSubscribeRecordDao');
    }

    /**
     * @return WeChatService
     */
    protected function getSDKWeChatService()
    {
        return $this->biz['qiQiuYunSdk.wechat'];
    }
}
