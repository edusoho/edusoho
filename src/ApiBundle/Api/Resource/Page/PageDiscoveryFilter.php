<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\ArrayToolkit;

class PageDiscoveryFilter extends Filter
{
    protected $publicFields = array('type', 'data');

    protected function publicFields(&$data)
    {
        if ('slide_show' == $data['type']) {
            $this->getFullImagePath($data['data']);
        }
        $courseSetFilter = new CourseSetFilter();
        $courseSetFilter->setMode(Filter::SIMPLE_MODE);
        if ('course_list' == $data['type']) {
            foreach ($data['data'] as &$single) {
                $single['items'] = $this->getHomepageCourses(
                    isset($single['items']['courses']) ? $single['items']['courses'] : array(), 
                    isset($single['items']['courseSets']) ? $single['items']['courseSets'] : array()
                );
                foreach ($single['items'] as &$items) {
                    $courseSetFilter->filter($items['courseSet']);
                }
            }
        }
    }

    protected function getFullImagePath(&$data)
    {
        foreach ($data as &$items) {
            $items['image'] = AssetHelper::uriForPath($items['image']);
        }
    }

    protected function getHomepageCourses($courses, $courseSets)
    {
        $tryLookVideoCourses = array_filter($courses, function ($course) {
            return !empty($course['tryLookVideo']);
        });
        $courses = ArrayToolkit::index($courses, 'courseSetId');
        $tryLookVideoCourses = ArrayToolkit::index($tryLookVideoCourses, 'courseSetId');

        array_walk($courseSets, function (&$courseSet) use ($courses, $tryLookVideoCourses) {
            if (isset($tryLookVideoCourses[$courseSet['id']])) {
                $courseSet['course'] = $tryLookVideoCourses[$courseSet['id']];
            } else {
                $courseSet['course'] = $courses[$courseSet['id']];
            }
        });
        $pageCourses = array();
        foreach ($courseSets as $courseSet) {
            $items = array(
                'id' => $courseSet['course']['id'],
                'price' => $courseSet['course']['price'],
                'courseSet' => $courseSet,
            );
            array_push($pageCourses, $items);
        }

        return $pageCourses;
    }
}