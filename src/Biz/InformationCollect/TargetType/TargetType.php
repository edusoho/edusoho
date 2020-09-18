<?php

namespace Biz\InformationCollect\TargetType;

use Biz\BaseService;

abstract class TargetType extends BaseService
{
    abstract public function getTargetInfo($targetIds);
}
