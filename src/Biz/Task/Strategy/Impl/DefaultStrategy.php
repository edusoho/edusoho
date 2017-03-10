<?php

namespace Biz\Task\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

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
            // 创建课时
            return $this->_createLesson($field);
        } else {
            // 创建课时中的环节
            return $this->_createLessonLink($field);
        }
    }

    public function updateTask($id, $fields)
    {
        $this->validateTaskMode($fields);
        $task = parent::updateTask($id, $fields);

        if ($task['mode'] == 'lesson') {
            $this->getCourseService()->updateChapter(
                $task['courseId'],
                $task['categoryId'],
                array('title' => $task['title'])
            );
        }

        return $task;
    }

    public function deleteTask($task)
    {
        if (empty($task)) {
            return;
        }
        try {
            $this->biz['db']->beginTransaction();
            $allTasks = array();
            if ($task['mode'] == 'lesson') {
                $allTasks = $this->getTaskDao()->findByCourseIdAndCategoryId(
                    $task['courseId'],
                    $task['categoryId']
                ); //courseId
            }
            foreach ($allTasks as $_task) {
                $this->getTaskDao()->delete($_task['id']);
                $this->getTaskResultService()->deleteUserTaskResultByTaskId($_task['id']);
                $this->getActivityService()->deleteActivity($_task['activityId']);
            }
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    protected function validateTaskMode($field)
    {
        if (empty($field['mode']) || !in_array(
                $field['mode'],
                array('preparation', 'lesson', 'exercise', 'homework', 'extraClass')
            )
        ) {
            throw new InvalidArgumentException('task mode  Invalid');
        }
    }

    public function prepareCourseItems($courseId, $tasks, $limitNum)
    {
        if ($limitNum) {
            $tasks = array_slice($tasks, 0, $limitNum);
        }
        $tasks = ArrayToolkit::group($tasks, 'categoryId');

        $items = array();
        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        foreach ($chapters as $chapter) {
            $chapter['itemType'] = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort(
            $items,
            function ($item1, $item2) {
                return $item1['seq'] > $item2['seq'];
            }
        );

        $taskCount = 1;
        foreach ($items as $key => $item) {
            if ($limitNum and $taskCount >= $limitNum) {
                unset($items[$key]);
            }
            if ($item['type'] != 'lesson') {
                continue;
            }

            if (!empty($tasks[$item['id']])) {
                $items[$key]['tasks'] = $tasks[$item['id']];
                $taskCount += count($tasks[$item['id']]);
            } else {
                unset($items[$key]);
            }
        }

        return $items;
    }

    public function sortCourseItems($courseId, array $ids)
    {
        $parentChapters = array(
            'lesson' => array(),
            'unit' => array(),
            'chapter' => array(),
        );

        $chapterTypes = array('chapter' => 3, 'unit' => 2, 'lesson' => 1);
        $lessonChapterTypes = array();
        $seq = 0;

        foreach ($ids as $key => $id) {
            if (strpos($id, 'chapter') !== 0) {
                continue;
            }
            $id = str_replace('chapter-', '', $id);
            $chapter = $this->getChapterDao()->get($id);
            ++$seq;

            $index = $chapterTypes[$chapter['type']];
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

            $chapter = $this->getCourseService()->updateChapter($courseId, $id, $fields);
            if ($chapter['type'] == 'lesson') {
                array_push($lessonChapterTypes, $chapter);
            }
            $parentChapters[$chapter['type']] = $chapter;
        }

        uasort(
            $lessonChapterTypes,
            function ($lesson1, $lesson2) {
                return $lesson1['seq'] > $lesson2['seq'];
            }
        );
        $taskNumber = 1;
        foreach ($lessonChapterTypes as $key => $chapter) {
            $tasks = $this->getTaskService()->findTasksByChapterId($chapter['id']);
            $tasks = ArrayToolkit::index($tasks, 'mode');
            foreach ($tasks as $task) {
                $seq = $this->getTaskSeq($task['mode'], $chapter['seq']);
                $fields = array(
                    'seq' => $seq,
                    'categoryId' => $chapter['id'],
                    'number' => $taskNumber,
                );
                $this->getTaskService()->updateSeq($task['id'], $fields);
                if ($task['mode'] == 'lesson') {
                    ++$taskNumber;
                }
            }
        }
    }

    //发布课时中一组任务
    public function publishTask($task)
    {
        $tasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
        foreach ($tasks as $task) {
            $this->getTaskDao()->update($task['id'], array('status' => 'published'));
        }
        $task['status'] = 'published';

        return $task;
    }

    //取消发布课时中一组任务
    public function unpublishTask($task)
    {
        $tasks = $this->getTaskDao()->findByChapterId($task['categoryId']);
        foreach ($tasks as $task) {
            $this->getTaskDao()->update($task['id'], array('status' => 'unpublished'));
        }
        $task['status'] = 'unpublished';

        return $task;
    }

    private function _createLesson($task)
    {
        $chapter = array(
            'courseId' => $task['fromCourseId'],
            'title' => $task['title'],
            'type' => 'lesson',
        );
        $chapter = $this->getCourseService()->createChapter($chapter);
        $task['categoryId'] = $chapter['id'];

        return parent::createTask($task);
    }

    private function _createLessonLink($task)
    {
        $lessonTask = $this->getTaskDao()->getByChapterIdAndMode($task['categoryId'], 'lesson');

        if (empty($lessonTask)) {
            throw new NotFoundException('lesson task is not found');
        }

        $task = parent::createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        return $this->getTaskService()->getTask($task['id']);
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
