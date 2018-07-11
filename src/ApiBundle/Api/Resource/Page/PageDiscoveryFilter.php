<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

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
                foreach ($single['items'] as &$course) {
                    $courseSetFilter->filter($course['courseSet']);
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
}