<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Course\CourseItemWithLessonFilter;
use ApiBundle\Api\Resource\CourseSet\CourseSetReviewFilter;
use ApiBundle\Api\Resource\Filter;

class PageCourseFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'courseSetTitle', 'goodsId', 'specsId', 'spec',
    ];
    protected $publicFields = [
        'vipLevel', 'access', 'learnMode', 'studentNum', 'allowAnonymousPreview', 'parentId', 'compulsoryTaskNum',
        'tryLookable', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'buyExpiryTime', 'summary',
        'audiences', 'goals', 'isDefault', 'maxStudentNum', 'status', 'isFree', 'price', 'originPrice', 'teachers',
        'creator', 'services', 'courseSet', 'courseItems', 'courses', 'member', 'courseType', 'progress', 'buyable',
        'reviews', 'myReview', 'enableFinish', 'hasCertificate', 'goodsId', 'specsId', 'spec', 'hitNum', 'classroom',
        'isReplayShow',
    ];

    protected function publicFields(&$data)
    {
        $member = $data['member'];
        if (isset($data['vipLevel']) && !empty($data['vipLevel'])) {
            $vipLevel = $data['vipLevel'];
        }

        $items = $data['courseItems'];
        foreach ($items as &$courseItem) {
            $courseItemFilter = new CourseItemWithLessonFilter();
            $courseItemFilter->setMode(Filter::PUBLIC_MODE);
            $courseItemFilter->filter($courseItem);
        }

        $courses = $data['courses'];
        foreach ($courses as &$course) {
            $courseFilter = new CourseFilter();
            $courseFilter->setMode(Filter::SIMPLE_MODE);
            $courseFilter->filter($course);
        }

        $reviews = $data['reviews'];
        $reviewFilter = new CourseSetReviewFilter();
        $reviewFilter->setMode(Filter::PUBLIC_MODE);
        $reviewFilter->filters($reviews);
        $review = $data['myReview'];
        $reviewFilter->filter($review);

        $progress = $data['progress'];
        $allowAnonymousPreview = $data['allowAnonymousPreview'];
        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::PUBLIC_MODE);
        $courseFilter->filter($data);
        $data['progress'] = $progress;
        $data['allowAnonymousPreview'] = $allowAnonymousPreview;
        $data['courseItems'] = $items;
        $data['courses'] = $courses;
        $data['member'] = $member;
        $data['reviews'] = $reviews;
        $data['myReview'] = $review;
        $data['vipLevel'] = empty($vipLevel) ? null : $vipLevel;

        if (!empty($data['classroom'])) {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::SIMPLE_MODE);
            $classroomFilter->filter($data['classroom']);
        }
    }
}
