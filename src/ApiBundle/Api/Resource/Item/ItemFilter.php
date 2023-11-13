<?php

namespace ApiBundle\Api\Resource\Item;

use ApiBundle\Api\Resource\Filter;
use Biz\Question\Traits\QuestionFormulaImgTrait;

class ItemFilter extends Filter
{
    use QuestionFormulaImgTrait;

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
        $item = $this->convertFormulaToImg($item);
        !empty($item['material']) && $item['material'] = $this->convertAbsoluteUrl($item['material']);
        !empty($item['analysis']) && $item['analysis'] = $this->convertAbsoluteUrl($item['analysis']);
        empty($item['analysis']) && $item['analysis'] = '';

        $questionFilter = new QuestionFilter();
        $questionFilter->filters($item['questions']);
    }
}
