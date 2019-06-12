<?php

namespace Biz\Activity\Type;

use Biz\Activity\ActivityException;
use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Activity\Service\TestpaperActivityService;

class Testpaper extends Activity
{
    protected function registerListeners()
    {
        return array(
            'activity.created' => 'Biz\Activity\Listener\TestpaperActivityCreateListener',
        );
    }

    public function get($targetId)
    {
        $activity = $this->getTestpaperActivityService()->getActivity($targetId);
        if ($activity) {
            $testPaper = $this->getTestpaperService()->getTestpaper($activity['mediaId']);
            $activity['testpaper'] = $testPaper;
        }

        return $activity;
    }

    public function find($ids, $showCloud = 1)
    {
        return $this->getTestpaperActivityService()->findActivitiesByIds($ids);
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getTestpaperActivityService()->createActivity($fields);
    }

    public function copy($activity, $config = array())
    {
        if ('testpaper' !== $activity['mediaType']) {
            return null;
        }

        $testpaperActivity = $this->get($activity['mediaId']);

        $newExt = array(
            'testpaperId' => empty($config['testId']) ? 0 : $config['testId'],
            'doTimes' => $testpaperActivity['doTimes'],
            'redoInterval' => $testpaperActivity['redoInterval'],
            'limitedTime' => $testpaperActivity['limitedTime'],
            'checkType' => $testpaperActivity['checkType'],
            'requireCredit' => $testpaperActivity['requireCredit'],
            'testMode' => $testpaperActivity['testMode'],
            'finishCondition' => $testpaperActivity['finishCondition'],
        );

        return $this->create($newExt);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceExt = $this->getTestpaperActivityService()->getActivity($sourceActivity['mediaId']);
        $ext = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $testPaper = $this->getTestpaperService()->getTestpaperByCopyIdAndCourseSetId(
            $sourceExt['mediaId'],
            $activity['fromCourseSetId']
        );

        $ext['testpaperId'] = $testPaper['id'];
        $ext['doTimes'] = $sourceExt['doTimes'];
        $ext['redoInterval'] = $sourceExt['redoInterval'];
        $ext['limitedTime'] = $sourceExt['limitedTime'];
        $ext['checkType'] = $sourceExt['checkType'];
        $ext['requireCredit'] = $sourceExt['requireCredit'];
        $ext['testMode'] = $sourceExt['testMode'];
        $ext['finishCondition'] = $sourceExt['finishCondition'];

        return $this->update($ext['id'], $ext, $activity);
    }

    public function update($targetId, &$fields, $activity)
    {
        $activity = $this->get($targetId);

        if (!$activity) {
            throw ActivityException::NOTFOUND_ACTIVITY();
        }

        //引用传递，当考试时间设置改变时，时间值也改变
        if (0 == $fields['doTimes'] || 'normal' == $fields['testMode']) {
            $fields['startTime'] = 0;
        }

        $filterFields = $this->filterFields($fields);

        return $this->getTestpaperActivityService()->updateActivity($activity['id'], $filterFields);
    }

    public function delete($targetId)
    {
        return $this->getTestpaperActivityService()->deleteActivity($targetId);
    }

    public function isFinished($activityId)
    {
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId, true);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId(
            $user['id'],
            $testpaperActivity['mediaId'],
            $activity['fromCourseId'],
            $activity['id'],
            'testpaper'
        );

        if (empty($result)) {
            return false;
        }
        if (!in_array(
            $result['status'],
            array('reviewing', 'finished')
        )) {
            return false;
        }

        if ('submit' === $activity['finishType']) {
            return true;
        }

        if ('score' === $activity['finishType'] && $result['score'] >= $testpaperActivity['finishCondition']['finishScore']) {
            return true;
        }

        return false;
    }

    protected function filterFields($fields)
    {
        if (!empty($fields['finishType'])) {
            if ('score' == $fields['finishType']) {
                $testPaper = $this->getTestpaperService()->getTestpaper($fields['testpaperId']);
                $fields['finishCondition'] = array(
                    'type' => 'score',
                    'finishScore' => empty($fields['finishData']) ? 0 : round($testPaper['score'] * $fields['finishData'], 0),
                );
            } else {
                $fields['finishCondition'] = array();
            }
        }

        $filterFields = ArrayToolkit::parts(
            $fields,
            array(
                'testpaperId',
                'doTimes',
                'redoInterval',
                'length',
                'limitedTime',
                'checkType',
                'requireCredit',
                'testMode',
                'finishCondition',
            )
        );

        if (isset($filterFields['length'])) {
            $filterFields['limitedTime'] = $filterFields['length'];
            unset($filterFields['length']);
        }

        if (isset($filterFields['doTimes']) && 0 == $filterFields['doTimes']) {
            $filterFields['testMode'] = 'normal';
        }

        $filterFields['mediaId'] = $filterFields['testpaperId'];
        unset($filterFields['testpaperId']);

        return $filterFields;
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }
}
