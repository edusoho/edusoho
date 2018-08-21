<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Course\CourseItemFilter;
use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Filter;

class PageCourseFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'courseSetTitle',
    );
    protected $publicFields = array(
        'access', 'learnMode', 'studentNum', 'allowAnonymousPreview', 'compulsoryTaskNum', 'tryLookable', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'buyExpiryTime', 'summary', 'audiences', 'goals', 'isDefault', 'maxStudentNum', 'status', 'isFree', 'price', 'originPrice', 'teachers', 'creator', 'services', 'courseSet', 'courseItems', 'courses', 'member', 'courseType', 'progress', 'buyable',
    );

    protected function publicFields(&$data)
    {
        $member = $data['member'];
        if (!empty($member)) {
            $courseMemberFilter = new CourseMemberFilter();
            $courseMemberFilter->setMode(Filter::PUBLIC_MODE);
            $courseMemberFilter->filter($member);
        }

        $courseItems = $this->convertToLeadingItems($data['courseItems'], $data['courseType'], false);
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

        $progress = $data['progress'];
        $allowAnonymousPreview = $data['allowAnonymousPreview'];
        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::PUBLIC_MODE);
        $courseFilter->filter($data);
        $data['progress'] = $progress;
        $data['allowAnonymousPreview'] = $allowAnonymousPreview;
        $data['courseItems'] = $courseItems;
        $data['member'] = $member;
        $data['courses'] = $courses;
    }

    private function convertToLeadingItems($originItems, $courseType, $onlyPublishTask = false)
    {
        $newItems = array();
        foreach ($originItems as $originItem) {
            $item = array();
            if ('task' == $originItem['itemType']) {
                $item['type'] = 'task';
                $item['seq'] = $originItem['seq'];
                $item['number'] = $originItem['number'];
                $item['title'] = $originItem['title'];
                $item['status'] = $originItem['status'];
                $item['task'] = $originItem;
                $newItems[] = $item;
                continue;
            }

            if ('chapter' == $originItem['itemType'] && 'lesson' == $originItem['type']) {
                if ('default' == $courseType) {
                    foreach ($originItem['tasks'] as $task) {
                        $item['type'] = 'task';
                        $item['seq'] = $task['seq'];
                        $item['number'] = $task['number'];
                        $item['title'] = $task['title'];
                        $item['status'] = $task['status'];
                        $item['task'] = $task;
                        $newItems[] = $item;
                    }
                }
                continue;
            }

            $item['type'] = $originItem['type'];
            $item['seq'] = $originItem['seq'];
            $item['number'] = $originItem['number'];
            $item['title'] = $originItem['title'];
            $item['status'] = $originItem['status'];
            $item['task'] = null;
            $newItems[] = $item;
        }

        return $onlyPublishTask ? $this->filterUnPublishTask($newItems) : $newItems;
    }

    private function filterUnPublishTask($items)
    {
        foreach ($items as $key => $item) {
            if ('task' == $item['type'] && 'published' != $item['task']['status']) {
                unset($items[$key]);
            }
        }

        return array_values($items);
    }
}
