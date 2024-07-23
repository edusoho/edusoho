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
        'category_name',
        'difficulty',
        'question_num',
        'isDelete',
        'seq',
        'score',
        'section_id',
        'includeImg',
        'attachments',
        'questions',
        'updated_time',
    ];

    protected function publicFields(&$item)
    {
        $item = $this->convertFormulaToImg($item);
        $item = $this->addItemEmphasisStyle($item);
        !empty($item['material']) && $item['material'] = $this->convertAbsoluteUrl($item['material']);
        !empty($item['analysis']) && $item['analysis'] = $this->convertAbsoluteUrl($item['analysis']);
        empty($item['analysis']) && $item['analysis'] = '';

        $questionFilter = new QuestionFilter();
        $questionFilter->filters($item['questions']);
    }
}
