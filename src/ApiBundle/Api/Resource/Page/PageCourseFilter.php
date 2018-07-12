<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Course\CourseItemFilter;
use ApiBundle\Api\Resource\Filter;

class PageCourseFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'courseSetTitle',
    );
    protected $publicFields = array(
        'access', 'learnMode', 'learnedNum', 'allowAnonymousPreview', 'compulsoryTaskNum', 'tryLookable', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'buyExpiryTime', 'summary', 'audiences', 'goals', 'isDefault', 'maxStudentNum', 'status', 'isFree', 'price', 'originPrice', 'teachers', 'creator', 'services', 'courseSet', 'courseItems', 'courses',
    );

    protected function publicFields(&$data)
    {
        $courseItems = $this->convertToLeadingItems($data['courseItems'], true);
        foreach ($courseItems as &$courseItem) {
            $courseItemFilter = new CourseItemFilter();
            $courseItemFilter->setMode(Filter::PUBLIC_MODE);
            $courseItemFilter->filter($courseItem);
        }

        $courses = $data['courses'];
        foreach ($courses as &$course) {
            $courseFilter = new CourseFilter();
            $courseFilter->setMode(Filter::SIMPLE_MODE);
            $courseFilter->filter($course);
        }

        $learnedNum = $data['learnedNum'];
        $allowAnonymousPreview = $data['allowAnonymousPreview'];
        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::PUBLIC_MODE);
        $courseFilter->filter($data);
        $data['learnedNum'] = $learnedNum;
        $data['allowAnonymousPreview'] = $allowAnonymousPreview;
        $data['courseItems'] = $courseItems;
        $data['courses'] = $courses;
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
}
