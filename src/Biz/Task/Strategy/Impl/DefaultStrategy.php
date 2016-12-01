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
        array_walk($items, function (&$item) use ($tasks) {
            if (!empty($tasks[$item['id']])) {
                $item['tasks'] = $tasks[$item['id']];
            } else {
                unset($item); //没有的tasks的数据是有问题的数据，
                $item['tasks'] = array();
            }
        });
        return $items;
    }


    //TODO 任务需要在排序时处理 chapterId， number
    public function sortCourseItems($courseId, array $ids)
    {
        $parentChapters = array(
            'lesson'  => array(),
            'unit'    => array(),
            'chapter' => array()
        );

        $chapterTypes = array('chapter' => 3, 'unit' => 2, 'lesson' => 1);

        $lessonNum = $chapterNum = $unitNum = $seq = 0;
        foreach ($ids as $key => $id) {
            if (strpos($id, 'chapter') === 0) {
                $id      = str_replace('chapter-', '', $id);
                $chapter = $this->getChapterDao()->get($id);
                //$seq = $key; //有业务含义，不是顺序增加的

                $index = $chapterTypes[$chapter['type']];
             //   var_dump($chapter, $index);
                $seq = $index == 1 ? $seq + 1 : $seq + 5;
                //    var_dump($index, $seq);
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
                $id = str_replace('task-', '', $id);

                foreach ($parentChapters as $parent) {
                    if (!empty($parent)) {
                        $this->getTaskService()->updateSeq($id, array(
                            'seq'        => $key,
                            'categoryId' => $parent['id']
                        ));
                        break;
                    }
                }
            }
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