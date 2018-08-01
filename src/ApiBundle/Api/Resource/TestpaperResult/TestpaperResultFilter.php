<?php

namespace ApiBundle\Api\Resource\TestpaperResult;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Testpaper\TestpaperItemFilter;

class TestpaperResultFilter extends Filter
{
    protected $publicFields = array('testpaper', 'items', 'accuracy', 'testpaperResult', 'favorites', 'resultShow');

    protected function publicFields(&$data)
    {
        if (!empty($data['items'])) {
            foreach ($data['items'] as $questionType => &$questions) {
                $questions = array_values($questions);
                foreach ($questions as &$question) {
                    $itemFilter = new TestpaperItemFilter();
                    $itemFilter->filter($question);
                }
            }
        }
    }
}
