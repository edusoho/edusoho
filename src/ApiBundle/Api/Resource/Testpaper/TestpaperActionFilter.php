<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\Resource\Course\CourseTaskFilter;
use ApiBundle\Api\Resource\Filter;

class TestpaperActionFilter extends Filter
{
    protected $publicFields = array('testpaperResult', 'testpaper', 'items', 'task');

    protected function publicFields(&$data)
    {
        if (!empty($data['items'])) {
            foreach ($data['items'] as $questionType => &$questions) {
                $questions = array_values($questions);
                foreach ($questions as &$question) {
                    unset($question['answer']);
                    unset($question['analysis']);
                    $itemFilter = new TestpaperItemFilter();
                    $itemFilter->filter($question);
                }
            }
        }
        if (!empty($data['testpaperResult'])) {
            $data['testpaperResult']['teacherSay'] = $this->convertAbsoluteUrl($data['testpaperResult']['teacherSay']);
        }

        if (!empty($data['task'])) {
            $tasKFilter = new CourseTaskFilter();
            $tasKFilter->filter($data['task']);
        }
    }

    /**
     * @param $array
     *
     * @return bool
     *              判断子数组和父级数组是否都为空
     */
    protected function isArrayEmpty($array)
    {
        foreach ($array as $key => $value) {
            if (!empty($value)) {
                return false;
            }
        }

        return true;
    }
}
