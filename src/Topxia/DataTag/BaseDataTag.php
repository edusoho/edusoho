<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

abstract class BaseDataTag
{

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}