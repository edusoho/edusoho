<?php
namespace Biz\Task\Strategy\Impl;

use Topxia\Common\ArrayToolkit;
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
            $chapter = array(
                'courseId' => $field['fromCourseId'],
                'title'    => $field['title'],
                'type'     => 'lesson'
            );
            $chapter             = $this->getCourseService()->createChapter($chapter);
            $field['categoryId'] = $chapter['id'];
        } else {
            $lessonTask = $this->getTaskDao()->getByChapterIdAndMode($field['categoryId'], 'lesson');
            if (empty($lessonTask)) {
                throw new NotFoundException('lesson task is not found');
            }
            $field['status'] = $lessonTask['status'];
        }

        $task = parent::createTask($field);

        $chapter          = $this->getChapterDao()->get($task['categoryId']);
        $tasks            = $this->getTaskService()->findTasksFetchActivityByChapterId($chapter['id']);
        $chapter['tasks'] = $tasks;
        $chapter['mode']  = $field['mode'];
        return $chapter;
    }

    public function updateTask($id, $fields)
    {
        $this->validateTaskMode($fields);
        $task = parent::updateTask($id, $fields);

        if ($task['mode'] == 'lesson') {
            $this->getCourseService()->updateChapter($task['courseId'], $task['categoryId'], array('title' => $task['title']));
        }

        return $task;
    }

    public function deleteTask($task)
    {
        try {
            $this->biz['db']->beginTransaction();
            if ($task['mode'] == 'lesson') {
                $this->getTaskDao()->deleteByCategoryId($task['categoryId']); //删除该课时下的所有任务，
                $this->getTaskResultService()->deleteUserTaskResultByTaskId($task['id']);
                $this->getActivityService()->deleteActivity($task['activityId']); //删除该课时
            } else {
                $this->getTaskDao()->delete($task['id']);
            }
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    protected function validateTaskMode($field)
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
        $taskNumber = 1;
        foreach ($lessonChapterTypes as $key => $chapter) {
            $tasks = $this->getTaskService()->findTasksByChapterId($chapter['id']);
            $tasks = ArrayToolkit::index($tasks, 'mode');
            foreach ($tasks as $task) {
                $seq    = $this->getTaskSeq($task['mode'], $chapter['seq']);
                $fields = array(
                    'seq'        => $seq,
                    'categoryId' => $chapter['id'],
                    'number'     => $taskNumber
                );
                $this->getTaskService()->updateSeq($task['id'], $fields);
                $taskNumber++;
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
    }

    //取消发布课时中一组任务
    public function unpublishTask($task)
    {
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
