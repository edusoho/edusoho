<?php

namespace Biz\Task\Strategy\Impl;

use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class PlanStrategy extends BaseStrategy implements CourseStrategy
{
    public function createTask($field)
    {
        $task = parent::createTask($field);

        $task['activity'] = $this->getActivityService()->getActivity($task['activityId'], $fetchMedia = true);
        return $task;
    }

    public function deleteTask($task)
    {
        $that = $this;
        $this->biz['db']->transactional(function () use ($task, $that) {
            $that->getTaskDao()->delete($task['id']);
            $that->getTaskResultService()->deleteUserTaskResultByTaskId($task['id']);
            $that->getActivityService()->deleteActivity($task['activityId']); //删除该课时
        });

        return true;
    }

    /**
     * 任务学习
     * @param  $task
     * @throws NotFoundException
     * @return bool
     */
    public function canLearnTask($task)
    {

        $course = $this->getCourseService()->getCourse($task['courseId']);

        //自由式学习 可以学习任意课时
        if ($course['learnMode'] == 'freeMode') {
            return true;
        }

        //选修任务不需要判断解锁条件
        if ($task['isOptional']) {
            return true;
        }

        if ($task['type'] == 'live') {
            return true;
        }

        if ($task['type'] == 'testpaper' and $task['startTime']) {
            return true;
        }

        //取得下一个发布的课时
        $conditions = array(
            'courseId' => $task['courseId'],
            'seq_LT'   => $task['seq']
        );

        $count    = $this->getTaskDao()->count($conditions);
        $preTasks = $this->getTaskDao()->search($conditions, array('seq' => 'DESC'), 0, $count);

        if (empty($preTasks)) {
            return true;
        }
        return $this->getTaskService()->isPreTasksIsFinished($preTasks);
    }

    public function getTasksRenderPage()
    {
        return 'course-manage/lock-mode/tasks.html.twig';
    }

    public function getTaskItemRenderPage()
    {
        return 'task-manage/list-item-lock-mode.html.twig';
    }

    public function prepareCourseItems($courseId, $tasks)
    {
        $items = array();
        foreach ($tasks as $task) {
            $task['itemType']            = 'task';
            $items["task-{$task['id']}"] = $task;
        }

        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        foreach ($chapters as $chapter) {
            $chapter['itemType']               = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        return $items;
    }


    public function sortCourseItems($courseId, array $itemIds)
    {
        $parentChapters = array(
            'lesson'  => array(),
            'unit'    => array(),
            'chapter' => array()
        );
        $taskNumber     = 0;
        $chapterTypes   = array('chapter' => 3, 'unit' => 2, 'lesson' => 1);
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
                $taskNumber++;
                $this->getTaskService()->updateSeq($id, array(
                    'seq'        => $key,
                    'categoryId' => $categoryId,
                    'number'     => $taskNumber
                ));
            }
        }
    }

    public function publishTask($task)
    {
        return $this->getTaskDao()->update($task['id'], array('status' => 'published'));
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
