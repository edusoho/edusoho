<?php
namespace Biz\Task\Strategy\Impl;

use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Biz\Task\Strategy\page;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Topxia\Common\ArrayToolkit;

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
            $task = $this->baseCreateTask($field);
        }
        return $task;
    }

    public function updateTask($id, $fields)
    {
        $this->validateTaskMode($fields);
        $task = $this->baseUpdateTask($id, $fields);

        if ($task['mode'] == 'lesson') {
            $this->getCourseService()->updateChapter($task['courseId'], $task['chapterId'], array('title' => $task['title']));
        }

        return $task;
    }

    public function deleteTask($task)
    {
        $that   = $this;
        $result = $this->biz['db']->transactional(function () use ($task, $that) {
            $currentSeq = $task['seq'];
            if ($task['mode'] == 'lesson') {
                $that->getTaskDao()->deleteByCategoryId($task['categoryId']); //删除该课时下的所有课程，
                $that->getActivityService()->deleteActivity($task['activityId']); //删除该课时
                $that->getChapterDao()->delete($task['categoryId']); //删除该课时
            } else {
                $that->getTaskDao()->delete($task['id']);
            }
            $that->getTaskDao()->waveSeqBiggerThanSeq($task['courseId'], $currentSeq, -1);
        });
        return $result;
    }


    public function getTasksRenderPage()
    {
        return 'WebBundle:CourseManage/FreeMode:tasks.html.twig';
    }

    /**
     * @param $field
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function validateTaskMode($field)
    {
        if (empty($field['mode']) || !in_array($field['mode'], array('preparation', 'lesson', 'exercise', 'homework', 'extraClass'))) {
            throw new InvalidArgumentException('task mode  Invalid');
        }
    }

    public function findCourseItems($courseId)
    {
        $tasks = $this->getTaskService()->findUserTasksFetchActivityAndResultByCourseId($courseId);
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
                throw new NotFoundException(json_encode($item));
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
        $taskNumber         = $seq = 0;

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

    protected function getTaskSeq($taskMode, $chapterSeq)
    {
        $taskModes = array('preparation' => 1, 'lesson' => 2, 'exercise' => 3, 'homework' => 4, 'extraClass' => 5);
        if (!in_array($taskMode, array_keys($taskModes))) {
            throw new InvalidArgumentException('task mode is invalida');
        }
        return $chapterSeq + $taskModes[$taskMode];
    }

}