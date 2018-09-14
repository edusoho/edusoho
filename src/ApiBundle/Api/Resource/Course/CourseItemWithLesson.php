<?php

namespace ApiBundle\Api\Resource\Course;

use AppBundle\Common\ArrayToolkit;

class CourseItemWithLesson extends CourseItem
{
    protected function convertToLeadingItems($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask = false)
    {
        $result = array();
        $lessonInfos = array();
        foreach ($originItems as $item) {
            if ('lesson' == $item['type']) {
                unset($item['tasks']);
                $lessonInfos[$item['id']] = $item;
            }
        }

        $convertedItems = parent::convertToLeadingItems($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask);
        foreach ($convertedItems as $key => $item) {
            if ('task' == $item['type']) {
                $lessonId = $item['task']['categoryId'];
                $lessonInfos[$lessonId]['tasks'][] = $item['task'];
            }
        }
        $lessonInfos = $onlyPublishTask ? $this->filterUnPublishLesson($lessonInfos) : $this->isHiddenUnpublishTasks($lessonInfos, $course['id']);
        $lessonInfos = ArrayToolkit::index($lessonInfos, 'id');

        $result = array();
        $lessonNum = 1;
        foreach ($convertedItems as $key => $item) {
            if ('task' == $item['type']) {
                $lessonId = $item['task']['categoryId'];
                if (!empty($lessonInfos[$lessonId])) {
                    $lessonItem = $lessonInfos[$lessonId];
                    $lessonItem['number'] = $lessonNum;
                    $result[] = $lessonItem;
                    ++$lessonNum;
                    unset($lessonInfos[$item['task']['categoryId']]);
                }
            } else {
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
            return $this->filterUnPublishLesson($lessonInfos);
        }

        return $lessonInfos;
    }
}
