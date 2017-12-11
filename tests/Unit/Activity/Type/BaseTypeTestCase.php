<?php

namespace Tests\Unit\Activity\Type;

use Biz\BaseTestCase;

class BaseTypeTestCase extends BaseTestCase
{
    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }
}
