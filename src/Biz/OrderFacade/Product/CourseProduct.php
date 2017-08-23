<?php

namespace Biz\OrderFacade\Product;

class CourseProduct extends Product
{
    const TYPE = 'course';

    public $type = self::TYPE;

    public function init(array $params)
    {
    }

    public function validate()
    {
    }
}
