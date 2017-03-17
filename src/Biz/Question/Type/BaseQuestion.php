<?php

namespace Biz\Question\Type;

use AppBundle\Common\ArrayToolkit;

class BaseQuestion
{
    public function filter(array $fields)
    {
        return ArrayToolkit::parts($fields, array(
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
            'courseSetId',
            'lessonId',
            'subCount',
            'finishedTimes',
            'passedTimes',
            'userId',
            'updatedTime',
            'createdTime',
        ));
    }
}
