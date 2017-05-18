<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use AppBundle\Common\Exception\InvalidArgumentException;

class Homework extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTestpaperService()->getTestpaperByIdAndType($targetId, 'homework');
    }

    public function find($targetIds)
    {
        return $this->getTestpaperService()->findTestpapersByIdsAndType($targetIds, 'homework');
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->buildTestpaper($fields, 'homework');
    }

    public function copy($activity, $config = array())
    {
        $newActivity = $config['newActivity'];
        $homework = $this->get($activity['mediaId']);
        $items = $this->getTestpaperService()->findItemsByTestId($homework['id']);

        $newHomework = array(
            'title' => $homework['name'],
            'description' => $homework['description'],
            'questionIds' => ArrayToolkit::column($items, 'questionId'),
            'passedCondition' => $homework['passedCondition'],
            'finishCondition' => $homework['passedCondition']['type'],
            'fromCourseId' => $newActivity['fromCourseId'],
            'fromCourseSetId' => $newActivity['fromCourseSetId'],
        );

        return $this->create($newHomework);
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
        return $this->getTestpaperService()->deleteTestpaper($targetId, true);
    }

    public function isFinished($activityId)
    {
        $biz = $this->getBiz();
        $user = $biz['user'];

        $activity = $this->getActivityService()->getActivity($activityId);
        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], 'homework');

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], 'homework');
        if (!$result) {
            return false;
        }

        if ($homework['passedCondition']['type'] === 'submit' && in_array($result['status'], array('reviewing', 'finished'))) {
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
            'finishCondition',
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
            'fromCourseSetId',
        ));

        if (!empty($filterFields['finishCondition'])) {
            $filterFields['passedCondition']['type'] = $filterFields['finishCondition'];
        }

        $filterFields['courseSetId'] = empty($filterFields['fromCourseSetId']) ? 0 : $filterFields['fromCourseSetId'];
        $filterFields['courseId'] = empty($filterFields['fromCourseId']) ? 0 : $filterFields['fromCourseId'];
        $filterFields['lessonId'] = 0;
        $filterFields['name'] = empty($filterFields['title']) ? '' : $filterFields['title'];

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
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
