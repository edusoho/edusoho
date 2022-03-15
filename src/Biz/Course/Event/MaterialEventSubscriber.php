<?php

namespace Biz\Course\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMaterialDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MaterialService;
use Biz\Task\Dao\TaskDao;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MaterialEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.material.create' => 'onCourseMaterialCreate',
            'course.material.update' => 'onCourseMaterialUpdate',
            'course.material.delete' => 'onCourseMaterialDelete',
            'course.lesson.materials.delete' => 'onCourseLessonMaterialsDelete',
            'course.task.material.update' => 'onCourseTaskMaterialUpdate',
        ];
    }

    public function onCourseTaskMaterialUpdate(Event $event)
    {
        $task = $event->getSubject();
        if ($task['copyId'] > 0) {
            return;
        }

        if ('download' != $task['type']) {
            return;
        }

        $this->updateMaterial($task);

        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($task['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }

        $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $copiedTasks = $this->getTaskDao()->findByCopyIdAndLockedCourseIds($task['id'], $copiedCourseIds);
        foreach ($copiedTasks as $copiedTask) {
            $activity = $this->getActivityService()->getActivity($copiedTask['activityId']);
            $sourceActivity = $this->getActivityService()->getActivity($activity['copyId']);
            $materials = $this->getMaterialService()->searchMaterials(['lessonId' => $sourceActivity['id'], 'courseId' => $sourceActivity['fromCourseId']], [], 0, PHP_INT_MAX);
            if (empty($materials)) {
                return;
            }

            $this->getMaterialDao()->deleteByLessonId($activity['id'], 'course');
            foreach ($materials as $material) {
                $newMaterial = $this->copyFields($material, [], [
                    'title',
                    'description',
                    'link',
                    'fileId',
                    'fileUri',
                    'fileMime',
                    'fileSize',
                    'source',
                    'userId',
                    'type',
                    'createdTime',
                ]);
                $newMaterial['copyId'] = $material['id'];
                $newMaterial['courseSetId'] = $copiedTask['fromCourseSetId'];
                $newMaterial['courseId'] = $copiedTask['courseId'];

                if ($material['lessonId'] > 0) {
                    $newMaterial['lessonId'] = $activity['id'];
                }

                $this->getMaterialDao()->create($newMaterial);
            }
        }
    }

    private function updateMaterial($task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $this->getMaterialDao()->deleteByLessonId($activity['id'], 'course');
        $materials = $this->object_to_array(json_decode($task['materials']));

        foreach ($materials as $material) {
            $newMaterial = $this->copyFields($material, [], [
                'title',
                'description',
                'link',
                'fileId',
                'fileUri',
                'fileMime',
                'fileSize',
                'source',
                'userId',
                'type',
                'createdTime',
            ]);

            $newMaterial['courseSetId'] = $task['fromCourseSetId'];
            $newMaterial['courseId'] = $task['fromCourseId'];
            $newMaterial['description'] = !empty($material['summary']) ? $material['summary'] : $material['description'];
            $newMaterial['lessonId'] = $activity['id'];
            if (empty($material['fileSize']) && $material['size']) {
                $file = $this->getUploadFileService()->getFile($material['fileId']);
                $material['fileSize'] = $file['fileSize'];
            }
            $newMaterial['fileSize'] = isset($material['fileSize']) ? $material['fileSize'] : $material['size'];
            $newMaterial['source'] = 'coursematerial';

            $this->getMaterialDao()->create($newMaterial);
        }
    }

    private function object_to_array($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        $arr = null;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val)) || is_object($val) ? $this->object_to_array($val) : $val;
            $arr[$key] = $val;
        }

        return $arr;
    }

    private function copyFields($source, $target, $fields)
    {
        if (empty($fields)) {
            return $target;
        }
        foreach ($fields as $field) {
            if (isset($source[$field])) {
                $target[$field] = $source[$field];
            }
        }

        return $target;
    }

    public function onCourseMaterialCreate(Event $event)
    {
        $this->updateMaterialNum($event);
    }

    public function onCourseMaterialUpdate(Event $event)
    {
        $this->updateMaterialNum($event);
    }

    public function onCourseMaterialDelete(Event $event)
    {
        $this->updateMaterialNum($event);
    }

    protected function updateMaterialNum($event)
    {
        $material = $event->getSubject();
        $this->getCourseService()->updateCourseStatistics($material['courseId'], ['materialNum']);
        if (!empty($material['courseSetId'])) {
            $this->getCourseSetService()->updateCourseSetStatistics($material['courseSetId'], ['materialNum']);
        }
    }

    public function onCourseLessonMaterialsDelete(Event $event)
    {
        $lesson = $event->getSubject();
        $activity = $this->getActivityService()->getActivity($lesson['lessonId']);
        $this->getCourseService()->updateCourseStatistics($activity['fromCourseId'], ['materialNum']);
        if (!empty($activity['fromCourseSetId'])) {
            $this->getCourseSetService()->updateCourseSetStatistics($activity['fromCourseSetId'], ['materialNum']);
        }
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->getBiz()->dao('Task:TaskDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->getBiz()->dao('Course:CourseMaterialDao');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->getBiz()->service('Course:MaterialService');
    }

    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
