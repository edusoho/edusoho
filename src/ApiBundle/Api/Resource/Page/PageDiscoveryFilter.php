<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class PageDiscoveryFilter extends Filter
{
    protected $publicFields = array('type', 'data', 'moduleType');

    protected function publicFields(&$data)
    {
        // if ('slide_show' == $data['type']) {
        //     $this->getFullPath($data['data']);
        // }
        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::PUBLIC_MODE);
        if ('course_list' == $data['type']) {
            foreach ($data['data']['items'] as &$course) {
                $courseFilter->filter($course);
            }
        }
    }

    // protected function getFullPath(&$data)
    // {
    //     foreach ($data as &$items) {
            
    //         $items['image'] = AssetHelper::uriForPath($items['image']);
    //         $items['link']['url'] = empty($items['link']['url']) ? AssetHelper::uriForPath('') : $items['link']['url'];
    //     }
    // }
}
