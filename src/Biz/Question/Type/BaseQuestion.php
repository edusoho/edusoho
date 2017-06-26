<?php

namespace Biz\Question\Type;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\BizAware;

class BaseQuestion extends BizAware
{
    public function filter(array $fields)
    {
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
            'courseSetId',
            'lessonId',
            'subCount',
            'finishedTimes',
            'passedTimes',
            'createdUserId',
            'updatedUserId',
            'updatedTime',
            'createdTime',
        ));

        if (!empty($fields['analysis'])) {
            $fields['analysis'] = $this->biz['html_helper']->purify($fields['analysis']);
        }

        return $fields;
    }
}
