<?php

namespace ApiBundle\Api\Resource\TestpaperResult;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Testpaper\TestpaperItemFilter;

class TestpaperResultFilter extends Filter
{
    protected $publicFields = array('testpaper', 'items', 'accuracy', 'testpaperResult', 'favorites', 'resultShow');

    protected function publicFields(&$data)
    {
        if (
            isset($data['testpaper']) &&
            isset($data['accuracy']) &&
            isset($data['testpaperResult']) &&
            'finished' == $data['testpaperResult']['status']
        ) {
            $rightItem = 0;
            $itemCount = 0;
            foreach ($data['accuracy'] as $questionType) {
                $rightItem += $questionType['right'];
                $itemCount += $questionType['right'] + $questionType['partRight'] + $questionType['wrong'] + $questionType['noAnswer'];
            }
            $data['testpaperResult']['rightRate'] = intval($rightItem / $itemCount * 100 + 0.5);
        }

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
