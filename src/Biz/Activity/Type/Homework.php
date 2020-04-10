<?php

namespace Biz\Activity\Type;

use Biz\Activity\Config\Activity;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class Homework extends Activity
{
    protected function registerListeners()
    {
        return [];
    }

    public function get($targetId)
    {
        return $this->getAssessmentService()->getAssessment($targetId);
    }

    public function find($targetIds, $showCloud = 1)
    {
        return $this->getAssessmentService()->findAssessmentsByIds($targetIds);
    }

    public function create($fields)
    {
        $items = $this->getItemService()->findItemsByIds($fields['questionIds'], true);
        $bankIds = array_column($items, 'bank_id');

        $homework = [
            'bank_id' => array_shift($bankIds),
            'name' => $fields['title'],
            'description' => $fields['description'],
            'status' => 'open',
            'displayable' => 0,
            'sections' => [
                [
                    'name' => '',
                    'items' => $items,
                ]
            ],
        ];

        $homework = $this->getAssessmentService()->createAssessment($homework);

        return $this->getAssessmentService()->openAssessment($homework['id']);
    }

    public function copy($activity, $config = [])
    {
        $homework = $this->get($activity['mediaId']);

        $items = $this->getAssessmentSectionItemService()->findSectionItemsByAssessmentId($homework['id']);

        $newHomework = [
            'title' => $homework['name'],
            'description' => $homework['description'],
            'questionIds' => array_column($items, 'item_id'),
        ];

        return $this->create($newHomework);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceHomework = $this->get($sourceActivity['mediaId']);

        $fields = [
            'name' => $sourceHomework['name'],
            'description' => $sourceHomework['description'],
        ];

        return $this->getAssessmentService()->updateBasicAssessment($activity['mediaId'], $fields);
    }

    public function update($targetId, &$fields, $activity)
    {
        $homework = $this->get($targetId);

        if (!$homework) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        $filterFields = [
            'name' => $fields['title'],
            'description' => $fields['description'],
        ];

        return $this->getAssessmentService()->updateBasicAssessment($homework['id'], $filterFields);
    }

    public function delete($targetId)
    {
        return $this->getAssessmentService()->deleteAssessment($targetId);
    }

    public function isFinished($activityId)
    {
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId);

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], 'homework');
        if (!$result) {
            return false;
        }

        if ('submit' === $activity['finishType'] && in_array($result['status'], array('reviewing', 'finished'))) {
            return true;
        }

        return false;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getAssessmentSectionItemService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemService');
    }
}
