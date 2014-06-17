<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\ExerciseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;

class ExerciseServiceImpl extends BaseService implements ExerciseService
{

    public function getExercise($id)
    {
        return $this->getExerciseDao()->getExercise($id);
    }

    public function createExercise($fields)
    {   
    
        if (!ArrayToolkit::requireds($fields, array('courseId', 'lessonId', 'question_number', 'source', 'diffculty', 'range'))) {
            throw $this->createServiceException('参数缺失，创建练习失败！');
        }

        $exercise = $this->getExerciseDao()->addExercise($this->filterExerciseFields($fields, 'create'));
        var_dump($exercise);exit();
        $items = $this->buildExercise($exercise['id'], $fields);

        return array($exercise, $items);
    }

    public function filterExerciseFields($fields, $mode)
    {
        $filtedFields = array();
        if ($mode == 'create') {
            $filtedFields['itemCount'] = $fields['question_number'];
            $filtedFields['source'] = $fields['source'];
            $filtedFields['courseId'] = $fields['courseId'];
            $filtedFields['lessonId'] = $fields['lessonId'];
            $filtedFields['diffculty'] = empty($fields['diffculty']) ? '' : $fields['diffculty'];
            $filtedFields['questionTypeRange'] = json_encode($fields['range']);
            $filtedFields['createdUserId'] = $this->getCurrentUser()->id;
            $filtedFields['createdTime']   = time();
        } else {
            if (array_key_exists('name', $fields)) {
                $filtedFields['name'] = empty($fields['name']) ? '' : $fields['name'];
            }

            if (array_key_exists('description', $fields)) {
                $filtedFields['description'] = empty($fields['description']) ? '' : $fields['description'];
            }

            if (array_key_exists('limitedTime', $fields)) {
                $filtedFields['limitedTime'] = empty($fields['limitedTime']) ? 0 : (int) $fields['limitedTime'];
            }
        }

        return $filtedFields;
    }

    private function getExerciseDao()
    {
        return $this->createDao('Course.ExerciseDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    private function getNotificationService()
    {
        return $this->createService('User.NotificationService');
    }

}