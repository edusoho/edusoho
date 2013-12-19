<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Service\Common\ServiceKernel;

abstract class BaseDataTag
{

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}
