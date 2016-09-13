<?php

namespace Topxia\Component\MediaParser\ItemParser;

use Topxia\Component\MediaParser\AbstractParser;

abstract class AbstractItemParser extends AbstractParser
{
	protected function getServiceKernel()
    {
            return ServiceKernel::instance();
     }
}