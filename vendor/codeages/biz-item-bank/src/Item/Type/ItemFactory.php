<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\ItemException;

class ItemFactory
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function create($type)
    {
        if (empty($this->biz['item_type.'.$type])) {
            throw new ItemException('type is not exist', ErrorCode::ITEM_ARGUMENT_INVALID);
        }

        return $this->biz['item_type.'.$type];
    }
}
