<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;

class Exercise extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTestpaperService()->getTestpaperByIdAndType($targetId, 'exercise');
    }

    public function find($targetIds)
    {
        return $this->getTestpaperService()->findTestpapersByIdsAndType($targetIds, 'exercise');
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->buildTestpaper($fields, 'exercise');
    }

    public function copy($activity, $config = array())
    {
        $newActivity = $config['newActivity'];
        $exercise = $this->get($activity['mediaId']);

        $newExercise = array(
            'title' => $exercise['name'],
            'itemCount' => $exercise['itemCount'],
            'difficulty' => !empty($exercise['metas']['difficulty']) ? $exercise['metas']['difficulty'] : 0,
            'questionTypes' => $exercise['metas']['questionTypes'],
            'finishCondition' => $exercise['passedCondition']['type'],
            'fromCourseId' => $newActivity['fromCourseId'],
            'courseSetId' => $newActivity['fromCourseSetId'],
        );

        $newExercise['range'] = empty($exercise['metas']['range']) || $exercise['metas']['range'] == 'lesson' ? 'course' : $exercise['metas']['range'];

        return $this->create($newExercise);
    }

    public function update($targetId, &$fields, $activity)
    {
        $exercise = $this->get($targetId);

        if (!$exercise) {
            throw $this->createNotFoundException('教学活动不存在');
        }

        $filterFields = $this->filterFields($fields);

        return $this->getTestpaperService()->updateTestpaper($exercise['id'], $filterFields);
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
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], 'exercise');

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], 'exercise');

        if (!$result) {
            return false;
        }

        if (!empty($exercise['passedCondition']) && $exercise['passedCondition']['type'] === 'submit' && in_array($result['status'], array('reviewing', 'finished'))) {
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
        $filterFields = ArrayToolkit::parts($fields, array(
            'title',
            'range',
            'itemCount',
            'difficulty',
            'questionTypes',
            'finishCondition',
            'fromCourseId',
            'fromCourseSetId',
            'courseSetId',
        ));

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
