<?php

namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class PlanStrategy extends BaseStrategy implements CourseStrategy
{
    public function createTask($field)
    {
        return $this->baseCreateTask($field);
    }

    public function updateTask($id, $fields)
    {
        return $this->baseUpdateTask($id, $fields);
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
        if ($this->isFirstTask($task)) {
            return true;
        }

        $preTask = $this->getTaskDao()->getByCourseIdAndSeq($task['courseId'], $task['seq'] - 1);

        if ($preTask['isOptional']) {
            return true;
        }
        if (empty($preTask)) {
            throw new NotFoundException('previous task does not exist');
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
        $taskNumber = 0;
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
                $id         = str_replace('task-', '', $id);

                foreach ($parentChapters as $parent) {
                    if (!empty($parent)) {
                        $taskNumber++;
                        $this->getTaskService()->updateSeq($id, array(
                            'seq'        => $key,
                            'categoryId' => $parent['id'],
                            'number'     => $taskNumber
                        ));
                        break;
                    }
                }
            }
        }
    }


    protected function isFirstTask($task)
    {
        return 1 == $task['seq'];
    }


}