<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;

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

    public function find($targetIds, $showCloud = 1)
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

        if ($config['isCopy']) {
            $items = $this->getTestpaperService()->findItemsByTestId($homework['id']);

            $copyIds = ArrayToolkit::column($items, 'questionId');
            $questions = $this->findQuestionsByCopydIdsAndCourseSetId($copyIds, $newActivity['fromCourseSetId']);
            $questionIds = ArrayToolkit::column($questions, 'id');
        } else {
            $items = $this->getTestpaperService()->findItemsByTestId($homework['id']);
            $questionIds = ArrayToolkit::column($items, 'questionId');
        }

        $newHomework = array(
            'title' => $homework['name'],
            'description' => $homework['description'],
            'questionIds' => $questionIds,
            'passedCondition' => $homework['passedCondition'],
            'fromCourseId' => $newActivity['fromCourseId'],
            'fromCourseSetId' => $newActivity['fromCourseSetId'],
            'copyId' => $config['isCopy'] ? $homework['id'] : 0,
        );

        return $this->create($newHomework);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceExercise = $this->get($sourceActivity['mediaId']);

        $fields = array(
            'name' => $sourceExercise['name'],
            'description' => $sourceExercise['description'],
        );

        return $this->getTestpaperService()->updateTestpaper($activity['mediaId'], $fields);
    }

    public function update($targetId, &$fields, $activity)
    {
        $homework = $this->get($targetId);

        if (!$homework) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
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
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId);
        $homework = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], 'homework');

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], 'homework');
        if (!$result) {
            return false;
        }

        if ('submit' === $activity['finishType'] && in_array($result['status'], array('reviewing', 'finished'))) {
            return true;
        }

        return false;
    }

    protected function filterFields($fields)
    {
        $filterFields = ArrayToolkit::parts($fields, array(
            'title',
            'description',
            'questionIds',
            'fromCourseId',
            'fromCourseSetId',
            'copyId',
            'passedCondition',
        ));
        if (!empty($fields['finishType'])) {
            $filterFields['passedCondition']['type'] = $fields['finishType'];
        }

        $filterFields['courseSetId'] = empty($filterFields['fromCourseSetId']) ? 0 : $filterFields['fromCourseSetId'];
        $filterFields['courseId'] = empty($filterFields['fromCourseId']) ? 0 : $filterFields['fromCourseId'];
        $filterFields['lessonId'] = 0;
        $filterFields['name'] = empty($filterFields['title']) ? '' : $filterFields['title'];

        return $filterFields;
    }

    protected function findQuestionsByCopydIdsAndCourseSetId($copyIds, $courseSetId)
    {
        if (empty($copyIds)) {
            return array();
        }

        $conditions = array(
            'copyIds' => $copyIds,
            'courseSetId' => $courseSetId,
        );

        return $this->getQuestionService()->search($conditions, array(), 0, PHP_INT_MAX);
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

    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }
}
