<?php

namespace ApiBundle\Api\Resource\Course;

use AppBundle\Common\ArrayToolkit;

class CourseItemWithLesson extends CourseItem
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

        $convertedItems = parent::convertToLeadingItems($originItems, $course, $onlyPublishTask);
        foreach ($convertedItems as $key => $item) {
            if ('task' == $item['type']) {
                $lessonInfos[$item['task']['categoryId']]['tasks'][] = $item;
            }
        }

        $lessonInfos = $onlyPublishTask ? $this->filterUnPublishTask($lessonInfos) : $this->isHiddenUnpublishTasks($lessonInfos, $course['id']);

        foreach ($lessonInfos as $lessonInfo) {
            $convertedItems[] = $lessonInfo;
        }

        $convertedItems = ArrayToolkit::sortPerArrayValue($convertedItems, 'seq');

        $result = array();
        $lessonNum = 1;
        foreach ($convertedItems as $key => $item) {
            if ('task' != $item['type']) {
                if ('lesson' == $item['type']) {
                    $item['number'] = $lessonNum;
                    ++$lessonNum;
                }
                $result[] = $item;
            }
        }

        return $result;
    }

    protected function filterUnPublishLesson($lessonInfos)
    {
        foreach ($lessonInfos as $itemKey => $item) {
            if ('published' != $item['status']) {
                unset($lessonInfos[$itemKey]);
            }
        }

        return $lessonInfos;
    }

    protected function isHiddenUnpublishTasks($lessonInfos, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if ($course['isHideUnpublish']) {
            return $this->filterUnPublishTask($lessonInfos);
        }

        return array_values($lessonInfos);
    }
}
