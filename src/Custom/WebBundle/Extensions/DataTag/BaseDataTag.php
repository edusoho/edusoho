<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/22
 * Time: 13:41
 */

namespace Custom\WebBundle\Extensions\DataTag;


class BaseDataTag extends \Topxia\WebBundle\Extensions\DataTag\BaseDataTag
{
    protected function createService($name)
    {
        return $this->getServiceKernel()->createService($name);
    }
}