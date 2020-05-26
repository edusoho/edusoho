<?php

namespace Codeages\Biz\ItemBank\Item\Wrapper;

use Codeages\Biz\ItemBank\Item\Type\Item;

class ItemWrapper
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function wrap($item, $withAnswer)
    {
        foreach ($item['questions'] as $k => &$question) {
            $question['seq'] = $k + 1;
            $question = $this->biz['question_wrapper']->wrap($question, $withAnswer);
            unset($question);
        }
        if (!$withAnswer) {
            unset($item['analysis']);
        }
        if (!$this->getItemProcessor($item['type'])->isAllowMaterials()) {
            $item['material'] = '';
        }

        return $item;
    }

    /**
     * @param $type
     *
     * @return Item
     */
    protected function getItemProcessor($type)
    {
        return $this->biz['item_type_factory']->create($type);
    }
}
