<?php

namespace Biz\Visualization\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Biz\Visualization\Dao\ActivityVideoWatchRecordDao;
use Biz\Visualization\Dao\UserActivityLearnFlowDao;
use Biz\Visualization\Service\DataCollectService;

class DataCollectServiceImpl extends BaseService implements DataCollectService
{
    //==============CLIENT==============

    const CLIENT_IOS = 'ios';

    const CLIENT_ANDROID = 'android';

    const CLIENT_MINIPROGRAM = 'miniprogram';

    const CLIENT_H5 = 'h5';

    const CLIENT_PC = 'pc';

    const CLIENT_WAP = 'wap';

    const CLIENT_UNKNOWN = 'unknown';

    const CLIENT_IOS_ID = 1;

    const CLIENT_ANDROID_ID = 2;

    const CLIENT_MINIPROGRAM_ID = 3;

    const CLIENT_H5_ID = 4;

    const CLIENT_PC_ID = 5;

    const CLIENT_WAP_ID = 6;

    const CLIENT_UNKNOWN_ID = 100;

    //============EVENT=============

    const EVENT_START = 'start';

    const EVENT_DOING = 'doing';

    const EVENT_FINISH = 'finish';

    const EVENT_START_ID = 1;

    const EVENT_DOING_ID = 2;

    const EVENT_FINISH_ID = 3;

    public function push($data)
    {
        if ('watching' === $data['event']) {
            return $this->watchingPush($data);
        }

        return $this->stayPush($data);
    }

    protected function watchingPush($data)
    {
        $data = ArrayToolkit::parts($data, [
            'courseId',
            'courseSetId',
            'taskId',
            'activityId',
            'userId',
            'startTime',
            'endTime',
            'duration',
            'client',
            'flowSign',
            'data',
            'status',
        ]);
        $data['client'] = $this->convertClient($data['client']);

        return $this->getActivityVideoWatchRecordDao()->create($data);
    }

    protected function stayPush($data)
    {
        $data = ArrayToolkit::parts($data, [
            'courseId',
            'courseSetId',
            'taskId',
            'activityId',
            'userId',
            'startTime',
            'endTime',
            'duration',
            'client',
            'event',
            'flowSign',
            'mediaType',
            'data',
            'status',
        ]);
        $data['client'] = $this->convertClient($data['client']);
        $data['event'] = $this->convertEvent($data['event']);

        return $this->getActivityLearnRecordDao()->create($data);
    }

    public function getFlowBySign($userId, $sign)
    {
        return $this->getUserActivityLearnFlowDao()->getByUserIdAndSign($userId, $sign);
    }

    public function createLearnFlow($userId, $activityId, $sign)
    {
        $learnFlow = [
            'userId' => $userId,
            'activityId' => $activityId,
            'sign' => $sign,
            'active' => 1,
            'startTime' => time(),
            'lastLearnTime' => time(),
        ];

        return $this->getUserActivityLearnFlowDao()->create($learnFlow);
    }

    public function updateLearnFlow($id, $flow)
    {
        $flow = ArrayToolkit::parts($flow, ['active', 'lastLearnTime', 'lastWatchTime']);

        return $this->getUserActivityLearnFlowDao()->update($id, $flow);
    }

    protected function convertClient($client)
    {
        $map = [
            self::CLIENT_IOS => self::CLIENT_IOS_ID,
            self::CLIENT_ANDROID => self::CLIENT_ANDROID_ID,
            self::CLIENT_MINIPROGRAM => self::CLIENT_MINIPROGRAM_ID,
            self::CLIENT_H5 => self::CLIENT_H5_ID,
            self::CLIENT_PC => self::CLIENT_PC_ID,
            self::CLIENT_WAP => self::CLIENT_WAP_ID,
            self::CLIENT_UNKNOWN => self::CLIENT_UNKNOWN_ID,
        ];

        return empty($map[$client]) ? self::CLIENT_UNKNOWN_ID : $map[$client];
    }

    protected function convertEvent($event)
    {
        $map = [
            self::EVENT_START => self::EVENT_START_ID,
            self::EVENT_DOING => self::EVENT_DOING_ID,
            self::EVENT_FINISH => self::EVENT_FINISH_ID,
        ];

        return $map[$event];
    }

    /**
     * @return ActivityVideoWatchRecordDao
     */
    protected function getActivityVideoWatchRecordDao()
    {
        return $this->createDao('Visualization:ActivityVideoWatchRecordDao');
    }

    /**
     * @return ActivityLearnRecordDao
     */
    protected function getActivityLearnRecordDao()
    {
        return $this->createDao('Visualization:ActivityLearnRecordDao');
    }

    /**
     * @return UserActivityLearnFlowDao
     */
    protected function getUserActivityLearnFlowDao()
    {
        return $this->createDao('Visualization:UserActivityLearnFlowDao');
    }
}
