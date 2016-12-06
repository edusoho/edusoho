<?php

namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Biz\Task\Strategy\新增任务的列表片段页面;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class PlanStrategy extends BaseStrategy implements CourseStrategy
{
    public function createTask($field)
    {
        $task = $this->baseCreateTask($field);

        $task['activity'] = $this->getActivityService()->getActivityFetchExt($task['activityId']);
        return $task;
    }

    public function updateTask($id, $fields)
    {
        return $this->baseUpdateTask($id, $fields);
    }

    public function deleteTask($task)
    {
        $that = $this;
        $this->biz['db']->transactional(function () use ($task, $that) {
            $that->getTaskDao()->delete($task['id']);
            $that->getActivityService()->deleteActivity($task['activityId']); //删除该课时
        });
        return true;
    }

    /**
     * 任务学习
     * @param $task
     * @return bool
     * @throws NotFoundException
     */
    public function canLearnTask($task)
    {
        $course = $this->getCourseService()->getCourse($task['courseId']);
        //自由式学习 可以学习任意课时
        if ($course['learnMode'] == 'freeMode') {
            return true;
        }
        //if the task is first return true;
        $preTask = $this->getTaskDao()->getPreTaskByCourseIdAndSeq($task['courseId'], $task['seq']);
        if (empty($preTask)) {
            return true;
        }

        if ($preTask['isOptional']) {
            return true;
        }

        $isTaskLearned = $this->getTaskService()->isTaskLearned($preTask['id']);
        if ($isTaskLearned) {
            return true;
        }

        return false;
    }

    public function getTasksRenderPage()
    {
        return 'WebBundle:CourseManage/LockMode:tasks.html.twig';
    }

    public function getTaskItemRenderPage()
    {
        return 'WebBundle:TaskManage:list-item-lockMode.html.twig';
    }


    public function findCourseItems($courseId)
    {
        return $this->baseFindCourseItems($courseId);
    }

    public function sortCourseItems($courseId, array $itemIds)
    {
        $parentChapters = array(
            'lesson'  => array(),
            'unit'    => array(),
            'chapter' => array()
        );

        $chapterTypes = array('chapter' => 3, 'unit' => 2, 'lesson' => 1);
        foreach ($itemIds as $key => $id) {
            if (strpos($id, 'chapter') === 0) {
                $id      = str_replace('chapter-', '', $id);
                $chapter = $this->getChapterDao()->get($id);
                $fields  = array('seq' => $key);

                $index = $chapterTypes[$chapter['type']];
                switch ($index) {
                    case 3:
                        $fields['parentId'] = 0;
                        break;
                    case 2:
                        if (!empty($parentChapters['chapter'])) {
                            $fields['parentId'] = $parentChapters['chapter']['id'];
                        }
                        break;
                    case 1:
                        if (!empty($parentChapters['unit'])) {
                            $fields['parentId'] = $parentChapters['unit']['id'];
                        } elseif (!empty($parentChapters['chapter'])) {
                            $fields['parentId'] = $parentChapters['chapter']['id'];
                        }
                        break;
                    default:
                        break;
                }

                if (!empty($parentChapters[$chapter['type']])) {
                    $fields['number'] = $parentChapters[$chapter['type']]['number'] + 1;
                } else {
                    $fields['number'] = 1;
                }

                foreach ($chapterTypes as $type => $value) {
                    if ($value < $index) {
                        $parentChapters[$type] = array();
                    }
                }

                $chapter                          = $this->getChapterDao()->update($id, $fields);
                $parentChapters[$chapter['type']] = $chapter;
            }
            if (strpos($id, 'task') === 0) {
                $categoryId = empty($chapter) ? 0 : $chapter['id'];
                $id         = str_replace('task-', '', $id);
                $this->getTaskService()->updateSeq($id, array(
                    'seq'        => $key,
                    'categoryId' => $categoryId,
                ));
            }
        }
    }

    public function publishTask($task)
    {
        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            throw $this->createAccessDeniedException('无权发布任务');
        }
        if ($task['status'] == 'published') {
            throw $this->createAccessDeniedException("task(#{$task['id']}) has been published");
        }
        $task = $this->getTaskDao()->update($task['id'], array('status' => 'published'));
        return $task;
    }

    public function unpublishTask($task)
    {
        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            throw $this->createAccessDeniedException('无权取消发布任务');
        }
        if ($task['status'] == 'unpublished') {
            throw $this->createAccessDeniedException("task(#{$task['id']}) has been  cancel published");
        }
        $task = $this->getTaskDao()->update($task['id'], array('status' => 'unpublished'));
        return $task;
    }


}