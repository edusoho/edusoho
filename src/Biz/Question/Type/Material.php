<?php

namespace Biz\Question\Type;

use Topxia\Common\ArrayToolkit;

class Material implements TypeInterface
{
    public function create($fields)
    {
    }

    public function update($targetId, $fields)
    {
    }

    public function delete($targetId)
    {
    }

    public function get($targetId)
    {
    }

    public function filter($fields)
    {
        if (!empty($fields['target']) && $fields['target'] > 0) {
            $fields['lessonId'] = $fields['target'];
            unset($fields['target']);
        }
        $fields = ArrayToolkit::parts($fields, array(
            'type',
            'stem',
            'difficulty',
            'userId',
            'answer',
            'analysis',
            'metas',
            'score',
            'categoryId',
            'parentId',
            'copyId',
            'target',
            'courseId',
            'lessonId',
            'subCount',
            'finishedTimes',
            'passedTimes',
            'userId',
            'updatedTime',
            'createdTime'
        ));

        return $fields;
    }
}
