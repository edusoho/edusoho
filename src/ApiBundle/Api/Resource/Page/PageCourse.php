<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ResponseFilter;

class PageCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     * @ResponseFilter(class="ApiBundle\Api\Resource\Page\PageCourseFilter", mode="public")
     */
    public function get(ApiRequest $request, $portal, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $this->getOCUtil()->single($course, array('creator', 'teacherIds'));
        $this->getOCUtil()->single($course, array('courseSetId'), 'courseSet');
        $course['access'] = $this->getCourseService()->canJoinCourse($courseId);
        $course['courseItems'] = $this->convertToLeadingItems($this->getCourseService()->findCourseItems($courseId), true);
        $course['courses'] = $this->getCourseService()->findPublishedCoursesByCourseSetId($course['courseSet']['id']);

        return $course;
    }

    private function convertToLeadingItems($originItems, $onlyPublishTask = false)
    {
        $newItems = array();
        $number = 1;
        foreach ($originItems as $originItem) {
            $item = array();
            if ('task' == $originItem['itemType']) {
                $item['type'] = 'task';
                $item['seq'] = $originItem['seq'];
                $item['number'] = strval($number++);
                $item['title'] = $originItem['title'];
                $item['task'] = $originItem;
                $newItems[] = $item;
                continue;
            }

            if ('chapter' == $originItem['itemType'] && 'lesson' == $originItem['type']) {
                foreach ($originItem['tasks'] as $task) {
                    $item['type'] = 'task';
                    $item['seq'] = $task['seq'];
                    $item['number'] = strval($number);
                    $item['title'] = $task['title'];
                    $item['task'] = $task;
                    $newItems[] = $item;
                }
                ++$number;
                continue;
            }

            $item['type'] = $originItem['type'];
            $item['seq'] = $originItem['seq'];
            $item['number'] = $originItem['number'];
            $item['title'] = $originItem['title'];
            $item['task'] = null;
            $newItems[] = $item;
        }

        return $onlyPublishTask ? $this->filterUnPublishTask($newItems) : $newItems;
    }

    private function filterUnPublishTask($items)
    {
        foreach ($items as $key => $item) {
            if ('task' == $item['type'] && $item['task']['status'] != 'published') {
                unset($items[$key]);
            }
        }

        return array_values($items);
    }

    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}
