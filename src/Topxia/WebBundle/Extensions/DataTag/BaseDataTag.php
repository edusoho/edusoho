<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Service\Common\ServiceKernel;

abstract class BaseDataTag
{

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}
