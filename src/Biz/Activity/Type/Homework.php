<?php

namespace Biz\Activity\Type;

use Biz\Activity\Dao\TestpaperActivityDao;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Topxia\Common\Exception\InvalidArgumentException;

class Homework extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTestpaperService()->getTestpaper($targetId);
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->buildTestpaper($fields, 'homework');
    }

    public function copy($activity, $config = array())
    {
        $ext    = $this->getTestpaperActivityDao()->get($activity['mediaId']);
        $newExt = array(
            'mediaId'         => $ext['testId'],
            'doTimes'         => 0,
            'redoInterval'    => $ext['redoInterval'],
            'limitedTime'     => $ext['limitedTime'],
            'checkType'       => $ext['checkType'],
            'finishCondition' => $ext['finishCondition'],
            'requireCredit'   => $ext['requireCredit'],
            'testMode'        => $ext['testMode']
        );

        return $this->getTestpaperActivityDao()->create($newExt);
    }

    public function update($targetId, &$fields, $activity)
    {
        $homework = $this->get($targetId);

        if (!$homework) {
            throw $this->createNotFoundException('教学活动不存在');
        }

        $filterFields = $this->filterFields($fields);

        return $this->getTestpaperService()->updateTestpaper($homework['id'], $filterFields);
    }

    public function delete($targetId)
    {
        return $this->getTestpaperService()->deleteTestpaper($targetId);
    }

    public function isFinished($activityId)
    {
        $biz  = $this->getBiz();
        $user = $biz['user'];

        $activity = $this->getActivityService()->getActivity($activityId);
        $homework = $this->getTestpaperService()->getTestpaper($activity['mediaId']);

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseSetId'], $activity['id'], 'homework');

        if (!$result) {
            return false;
        }

        if ($homework['passedCondition']['type'] == 'submit' && in_array($result['status'], array('reviewing', 'finished'))) {
            return true;
        }

        return false;
    }

    protected function getListeners()
    {
        return array();
    }

    protected function filterFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array(
            'finishCondition'
        ))
        ) {
            throw new InvalidArgumentException('homework fields is invalid');
        }

        $filterFields = ArrayToolkit::parts($fields, array(
            'title',
            'description',
            'questionIds',
            'passedCondition',
            'finishCondition',
            'fromCourseId',
            'fromCourseSetId'
        ));

        if (!empty($filterFields['finishCondition'])) {
            $filterFields['passedCondition']['type'] = $filterFields['finishCondition'];
        }

        $filterFields['courseSetId'] = empty($filterFields['fromCourseSetId']) ? 0 : $filterFields['fromCourseSetId'];
        $filterFields['courseId']    = empty($filterFields['fromCourseId']) ? 0 : $filterFields['fromCourseId'];
        $filterFields['lessonId']    = 0;
        $filterFields['name']        = empty($filterFields['title']) ? '' : $filterFields['title'];

        return $filterFields;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service("Activity:ActivityService");
    }

    /**
     * @return TestpaperActivityDao
     */
    protected function getTestpaperActivityDao()
    {
        return $this->getBiz()->dao('Activity:TestpaperActivityDao');
    }
}
