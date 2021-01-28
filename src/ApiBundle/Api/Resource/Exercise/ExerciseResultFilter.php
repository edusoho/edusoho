<?php

namespace ApiBundle\Api\Resource\Exercise;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Testpaper\TestpaperItemFilter;

class ExerciseResultFilter extends Filter
{
    protected $publicFields = array(
        'id', 'paperName', 'testId', 'userId', 'score', 'objectiveScore', 'subjectiveScore', 'teacherSay', 'rightItemCount', 'passedStatus',
        'limitedTime', 'beginTime', 'endTime', 'status', 'checkTeacherId', 'checkedTime', 'usedTime', 'migrateResultId', 'items', 'rightRate',
    );

    protected $simpleFields = array(
        'id', 'status', 'beginTime', 'endTime',
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['items'])) {
            $itemFilter = new TestpaperItemFilter();
            $itemFilter->filters($data['items']);
        }

        $data['teacherSay'] = $this->convertAbsoluteUrl($data['teacherSay']);
        $data['checkedTime'] = $data['endTime'] > 0 ? date('c', $data['checkedTime']) : 0;
    }

    protected function simpleFields(&$data)
    {
        $data['beginTime'] = date('c', $data['beginTime']);
        $data['endTime'] = $data['endTime'] > 0 ? date('c', $data['endTime']) : 0;
    }
}
