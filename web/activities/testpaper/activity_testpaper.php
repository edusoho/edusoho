<?php

require_once 'dao/testpaper_activity_dao.php';

use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;

class activity_testpaper extends Activity
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
        $testPaper = $this->getTestpaperService()->getTestpaper($activity['mediaId']);
        $activity['testpaper'] = $testPaper;

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

        $ext = $this->get($activity['mediaId']);

        $newExt = array(
            'mediaId' => $config['testId'],
            'doTimes' => $ext['doTimes'],
            'redoInterval' => $ext['redoInterval'],
            'limitedTime' => $ext['limitedTime'],
            'checkType' => $ext['checkType'],
            'requireCredit' => $ext['requireCredit'],
            'testMode' => $ext['testMode'],
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

        $ext['mediaId'] = $testPaper['id'];
        $ext['doTimes'] = $sourceExt['doTimes'];
        $ext['redoInterval'] = $sourceExt['redoInterval'];
        $ext['limitedTime'] = $sourceExt['limitedTime'];
        $ext['checkType'] = $sourceExt['checkType'];
        $ext['requireCredit'] = $sourceExt['requireCredit'];
        $ext['testMode'] = $sourceExt['testMode'];

        return $this->getTestpaperActivityService()->updateActivity($ext['id'], $ext);
    }

    public function update($targetId, &$fields, $activity)
    {
        $activity = $this->get($targetId);

        if (!$activity) {
            throw $this->createNotFoundException('教学活动不存在');
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
        $biz = $this->getBiz();
        $user = $biz['user'];

        $activity = $this->getActivityService()->getActivity($activityId);
        $testpaper = $activity['ext']['testpaper'];

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId(
            $user['id'],
            $testpaperActivity['mediaId'],
            $activity['fromCourseId'],
            $activity['id'],
            'testpaper'
        );

        if (!empty($result)) {
            return false;
        }

        if (in_array(
                $result['status'],
                array('reviewing', 'finished')
            ) && 'submit' === $activity['finishType']
        ) {
            return true;
        }

        $passScore = ceil($testpaper['score'] * $activity['finishData']);
        if (in_array(
                $result['status'],
                array('reviewing', 'finished')
            ) && 'score' === $activity['finishType'] && $result['score'] >= $passScore
        ) {
            return true;
        }

        return false;
    }

    protected function filterFields($fields)
    {
        $filterFields = ArrayToolkit::parts(
            $fields,
            array(
                'testpaperId',
                'doTimes',
                'redoInterval',
                'length',
                'limitedTime',
                'checkType',
                'finishScore',
                'requireCredit',
                'testMode',
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
