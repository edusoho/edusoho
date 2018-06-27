<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\LiveActivityService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class Live extends Activity
{
    protected function registerListeners()
    {
        return array(
            'watching' => 'Biz\Activity\Listener\LiveActivityWatchListener',
        );
    }

    public function preCreateCheck($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('fromCourseId', 'startTime', 'length'), true)) {
            throw new InvalidArgumentException('activity.missing_params');
        }

        $overlapTimeActivities = $this->getActivityDao()->findOverlapTimeActivitiesByCourseId(
            $fields['fromCourseId'],
            $fields['startTime'],
            $fields['startTime'] + $fields['length'] * 60
        );

        if ($overlapTimeActivities) {
            throw new InvalidArgumentException('activity.live.overlap_time');
        }
    }

    public function preUpdateCheck($activity, $newFields)
    {
        if (empty($newFields['startTime']) || empty($newFields['length'])) {
            return;
        }

        if (!ArrayToolkit::requireds($newFields, array('fromCourseId', 'startTime', 'length'), true)) {
            throw new InvalidArgumentException('activity.missing_params');
        }

        $overlapTimeActivities = $this->getActivityDao()->findOverlapTimeActivitiesByCourseId(
            $newFields['fromCourseId'],
            $newFields['startTime'],
            $newFields['startTime'] + $newFields['length'] * 60,
            $activity['id']
        );

        if ($overlapTimeActivities) {
            throw new InvalidArgumentException('activity.live.overlap_time');
        }
    }

    public function create($fields)
    {
        return $this->getLiveActivityService()->createLiveActivity($fields);
    }

    public function copy($activity, $config = array())
    {
        $biz = $this->getBiz();
        $live = $this->getLiveActivityService()->getLiveActivity($activity['mediaId']);
        if (empty($config['refLiveroom'])) {
            $activity['fromUserId'] = $biz['user']['id'];
            unset($activity['id']);
            unset($activity['startTime']);
            unset($activity['endTime']);

            return $this->getLiveActivityService()->createLiveActivity($activity, true);
        }

        return $live;
    }

    public function sync($sourceActivity, $activity)
    {
        //引用的是同一个直播教室，无需同步
        return null;
    }

    public function allowTaskAutoStart($activity)
    {
        return $activity['startTime'] <= time();
    }

    public function update($id, &$fields, $activity)
    {
        return $this->getLiveActivityService()->updateLiveActivity($id, $fields, $activity);
    }

    public function get($targetId)
    {
        return $this->getLiveActivityService()->getLiveActivity($targetId);
    }

    public function find($targetIds)
    {
        return $this->getLiveActivityService()->findLiveActivitiesByIds($targetIds);
    }

    public function delete($targetId)
    {
        $target = $this->get($targetId);
        $conditions['mediaId'] = $target['mediaId'];
        $count = $this->getActivityService()->count($conditions);
        if (1 == $count) {
            return $this->getLiveActivityService()->deleteLiveActivity($targetId);
        }
    }

    public function allowEventAutoTrigger()
    {
        return false;
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->getBiz()->service('Activity:LiveActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->getBiz()->dao('Activity:ActivityDao');
    }
}
