<?php
namespace Biz\Task\Strategy\Impl;

use Topxia\Common\ArrayToolkit;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

/**
 * 自由学习策略
 * Class DefaultStrategy
 * @package Biz\Task\Strategy\Impl
 */
class DefaultStrategy extends BaseStrategy implements CourseStrategy
{
    public function canLearnTask($task)
    {
        return true;
    }

    //如果不是课时任务，则需要根据课时任务的状态设置状态，即如果发布了，该任务创建时，已经是发布状态， 如果没有发布，则就是创建
    public function createTask($field)
    {
        $this->validateTaskMode($field);
        if ($field['mode'] == 'lesson') {
            $that = $this;

            $chapter = array(
                'courseId' => $field['fromCourseId'],
                'title'    => $field['title'],
                'type'     => 'lesson'
            );
            $task    = $this->biz['db']->transactional(function () use ($field, $chapter, $that) {
                $chapter             = $that->getCourseService()->createChapter($chapter);
                $field['categoryId'] = $chapter['id'];
                $task                = $that->baseCreateTask($field);
                return $task;
            });
        } else {
            $lessonTask = $this->getTaskDao()->getByChapterIdAndMode($field['categoryId'], 'lesson');
            if (empty($lessonTask)) {
                throw new NotFoundException('lesson task is not found');
            }
            $field['status'] = $lessonTask['status'];
            $task            = $this->baseCreateTask($field);
        }
        $chapter          = $this->getChapterDao()->get($task['categoryId']);
        $tasks            = $this->getTaskService()->findTasksFetchActivityByChapterId($chapter['id']);
        $chapter['tasks'] = $tasks;
        $task['mode']     = $field['mode'];

        return $chapter;
    }

    public function updateTask($id, $fields)
    {
        $this->validateTaskMode($fields);
        $task = $this->baseUpdateTask($id, $fields);
        if ($task['mode'] == 'lesson') {
            $this->getCourseService()->updateChapter($task['courseId'], $task['categoryId'], array('title' => $task['title']));
        }

        return $task;
    }

    public function deleteTask($task)
    {
        $that   = $this;
        $result = $this->biz['db']->transactional(function () use ($task, $that) {
            if ($task['mode'] == 'lesson') {
                $that->getTaskDao()->deleteByCategoryId($task['categoryId']); //删除该课时下的所有课程，
                $that->getTaskResultService()->deleteUserTaskResultByTaskId($task['id']);
                $that->getActivityService()->deleteActivity($task['activityId']); //删除该课时
            } else {
                $that->getTaskDao()->delete($task['id']);
            }
        });
        return $result;
    }

    public function getTasksRenderPage()
    {
        return 'course-manage/free-mode/tasks.html.twig';
    }

    public function getTaskItemRenderPage()
    {
        return 'task-manage/list-item.html.twig';
    }

    /**
     * @param  $field
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function validateTaskMode($field)
    {
        if (empty($field['mode']) || !in_array($field['mode'], array('preparation', 'lesson', 'exercise', 'homework', 'extraClass'))) {
            throw new InvalidArgumentException('task mode  Invalid');
        }
    }

    public function prepareCourseItems($courseId, $tasks)
    {
        $tasks = ArrayToolkit::group($tasks, 'categoryId');

        $items    = array();
        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        foreach ($chapters as $chapter) {
            $chapter['itemType']               = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        foreach ($items as $key => $item) {
            if ($item['type'] != 'lesson') {
                continue;
            }
            if (!empty($tasks[$item['id']])) {
                $items[$key]['tasks'] = $tasks[$item['id']];
            } else {
                unset($items[$key]);
                //throw new NotFoundException(json_encode($item));
            }
        }
        return $items;
    }

    public function sortCourseItems($courseId, array $ids)
    {
        $parentChapters = array(
            'lesson'  => array(),
            'unit'    => array(),
            'chapter' => array()
        );

        $chapterTypes       = array('chapter' => 3, 'unit' => 2, 'lesson' => 1);
        $lessonChapterTypes = array();
        $seq                = 0;

        foreach ($ids as $key => $id) {
            if (strpos($id, 'chapter') !== 0) {
                continue;
            }
            $id      = str_replace('chapter-', '', $id);
            $chapter = $this->getChapterDao()->get($id);
            $seq++;

            $index  = $chapterTypes[$chapter['type']];
            $fields = array('seq' => $seq);

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
                    $seq += 5;
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

            $chapter = $this->getChapterDao()->update($id, $fields);
            if ($chapter['type'] == 'lesson') {
                array_push($lessonChapterTypes, $chapter);
            }
            $parentChapters[$chapter['type']] = $chapter;
        }

        uasort($lessonChapterTypes, function ($lesson1, $lesson2) {
            return $lesson1['seq'] > $lesson2['seq'];
        });
        $taskNumber = 0;
        foreach ($lessonChapterTypes as $key => $chapter) {
            $tasks = $this->getTaskService()->findTasksByChapterId($chapter['id']);
            $tasks = ArrayToolkit::index($tasks, 'mode');
            foreach ($tasks as $task) {
                $taskNumber++;
                $seq    = $this->getTaskSeq($task['mode'], $chapter['seq']);
                $fields = array(
                    'seq'        => $seq,
                    'categoryId' => $chapter['id'],
                    'number'     => $taskNumber
                );

                $this->getTaskService()->updateSeq($task['id'], $fields);
            }
        }
    }

    //发布课时中一组任务
    public function publishTask($task)
    {
        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            throw $this->createAccessDeniedException('无权删除任务');
        }
        if ($task['status'] == 'published') {
            throw $this->createAccessDeniedException("task(#{$task['id']}) has been published");
        }

        $tasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
        foreach ($tasks as $task) {
            $this->getTaskDao()->update($task['id'], array('status' => 'published'));
        }
    }

    //取消发布课时中一组任务
    public function unpublishTask($task)
    {
        if (!$this->getCourseService()->tryManageCourse($task['courseId'])) {
            throw $this->createAccessDeniedException('无权删除任务');
        }
        if ($task['status'] == 'unpublished') {
            throw $this->createAccessDeniedException("task(#{$task['id']}) has been  cancel published");
        }

        $tasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
        foreach ($tasks as $key => $task) {
            $this->getTaskDao()->update($task['id'], array('status' => 'unpublished'));
        }
    }

    protected function getTaskSeq($taskMode, $chapterSeq)
    {
        $taskModes = array('preparation' => 1, 'lesson' => 2, 'exercise' => 3, 'homework' => 4, 'extraClass' => 5);
        if (!in_array($taskMode, array_keys($taskModes))) {
            throw new InvalidArgumentException('task mode is invalida');
        }
        return $chapterSeq + $taskModes[$taskMode];
    }
}
