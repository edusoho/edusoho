<?php
namespace Biz\Task\Strategy\Impl;


use Biz\Task\Strategy\BaseLearningStrategy;
use Biz\Task\Strategy\LearningStrategy;
use Biz\Task\Strategy\page;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

/**
 * 自由学习策略
 * Class FreeOrderStrategy
 * @package Biz\Task\Strategy\Impl
 */
class FreeModeStrategy extends BaseLearningStrategy implements LearningStrategy
{

    public function canLearnTask($task)
    {
        return true;
    }

    public function createTask($field)
    {
        $this->validateTaskMode($field);
        $task = $this->baseCreateTask($field);

        if ($task['mode'] == 'lesson') {
            $chapter = $this->prepareChapterFields($task);
            $this->getCourseService()->createChapter($chapter);
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


    public function findCourseItems($courseId)
    {
        return $this->baseFindCourseItems($courseId);
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


    protected function canCreateChapter()
    {

    }

    protected function prepareChapterFields($task)
    {
        return array(
            'courseId' => $task['courseId'],
            'title'    => $task['title'],
            'type'     => 'lesson'
        );
    }
}