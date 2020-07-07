<?php

namespace ApiBundle\Api\Resource\Item;

use ApiBundle\Api\Resource\Filter;

class ItemFilter extends Filter
{
    protected $publicFields = [
        'id',
        'bank_id',
        'type',
        'material',
        'analysis',
        'category_id',
        'difficulty',
        'question_num',
        'isDelete',
        'seq',
        'score',
        'section_id',
        'includeImg',
        'attachments',
        'questions',
    ];

    protected function publicFields(&$item)
    {
        $questionFilter = new QuestionFilter();
        !empty($item['material']) && $item['material'] = $this->convertAbsoluteUrl($item['material']);
        !empty($item['analysis']) && $item['analysis'] = $this->convertAbsoluteUrl($item['analysis']);
        $questionFilter->filters($item['questions']);
    }
}
