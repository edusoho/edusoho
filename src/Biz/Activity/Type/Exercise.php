<?php

namespace Biz\Activity\Type;

use Biz\Activity\ActivityException;
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

    public function find($targetIds, $showCloud = 1)
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
            'passedCondition' => $exercise['passedCondition'],
            'fromCourseId' => $newActivity['fromCourseId'],
            'courseSetId' => $newActivity['fromCourseSetId'],
            'metas' => $exercise['metas'],
            'copyId' => $config['isCopy'] ? $exercise['id'] : 0,
        );

        return $this->create($newExercise);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceExercise = $this->get($sourceActivity['mediaId']);

        $fields = array(
            'name' => $sourceExercise['name'],
            'passedCondition' => $sourceExercise['passedCondition'],
            'itemCount' => $sourceExercise['itemCount'],
            'metas' => $sourceExercise['metas'],
        );

        return $this->getTestpaperService()->updateTestpaper($activity['mediaId'], $fields);
    }

    public function update($targetId, &$fields, $activity)
    {
        $exercise = $this->get($targetId);

        if (!$exercise) {
            throw ActivityException::NOTFOUND_ACTIVITY();
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
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId);
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], 'exercise');

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], 'exercise');

        if (!$result) {
            return false;
        }

        if (!empty($exercise['passedCondition']) && 'submit' === $activity['finishType'] && in_array($result['status'], array('reviewing', 'finished'))) {
            return true;
        }

        return false;
    }

    protected function filterFields($fields)
    {
        $filterFields = ArrayToolkit::parts($fields, array(
            'title',
            'range',
            'itemCount',
            'difficulty',
            'questionTypes',
            'passedCondition',
            'fromCourseId',
            'fromCourseSetId',
            'courseSetId',
            'courseId',
            'lessonId',
            'metas',
            'copyId',
        ));

        $filterFields['courseId'] = empty($filterFields['fromCourseId']) ? 0 : $filterFields['fromCourseId'];
        $filterFields['name'] = empty($filterFields['title']) ? '' : $filterFields['title'];

        if (!empty($fields['finishType'])) {
            $filterFields['passedCondition']['type'] = $fields['finishType'];
        }

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
