<?php
namespace Biz\Task\Strategy\Impl;

use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Biz\Task\Strategy\page;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Topxia\Common\ArrayToolkit;

/**
 * 自由学习策略
 * Class FreeOrderStrategy
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
            $chapter             = $this->prepareChapterFields($field);
            $chapter             = $this->getCourseService()->createChapter($chapter);
            $field['categoryId'] = $chapter['id'];
        }
        $task = $this->baseCreateTask($field);
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
        array_walk($items, function (&$item) use ($tasks) {
            if (!empty($tasks[$item['id']])) {
                $item['tasks'] = $tasks[$item['id']];
            } else {
                $item['tasks'] = array();
            }
        });
        return $items;
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


    protected function prepareChapterFields($task)
    {
        return array(
            'courseId' => $task['fromCourseId'],
            'title'    => $task['title'],
            'type'     => 'lesson'
        );
    }
}