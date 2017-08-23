<?php

namespace Biz\OrderFacade\Product;

class ClassroomProduct extends Product
{
    const TYPE = 'classroom';

    public $type = self::TYPE;

    public function init(array $params)
    {
    }

    public function validate()
    {
    }
}
