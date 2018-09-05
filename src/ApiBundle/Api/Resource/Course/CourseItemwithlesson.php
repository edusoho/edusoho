<?php

namespace ApiBundle\Api\Resource\Course;

class CourseItemwithlesson extends CourseItem
{
    protected function convertToLeadingItems($originItems, $course, $onlyPublishTask = false)
    {
        $result = array();
        $lessonInfos = array();
        foreach ($originItems as $item) {
            if ('lesson' == $item['type']) {
                $lessonInfos[$item['id']] = $item;
            }
        }

        foreach ($originItems as $item) {
            if ('task' == $item['itemType']) {
                $lessonInfos[$item['categoryId']]['tasks'][] = $item;
            }
        }

        foreach ($originItems as $item) {
            if ('lesson' == $item['type']) {
                $result[] = $lessonInfos[$item['id']];
            } elseif ('chapter' == $item['itemType']) {
                $result[] = $item;
            }
        }

        return $onlyPublishTask ? $this->filterUnPublishTask($result) : $this->isHiddenUnpublishTasks($result, $course['id']);
    }

    protected function filterUnPublishTask($items)
    {
        foreach ($items as $itemKey => $item) {
            if ('lesson' == $item['type']) {
                foreach ($item['tasks'] as $taskKey => $task) {
                    if ('published' != $task['status']) {
                        unset($items[$itemKey]['tasks'][$taskKey]);
                    }
                }
            }
        }

        return array_values($items);
    }
}
