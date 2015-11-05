<?php
namespace Mooc\WebBundle\Extensions\DataTag;


class BaseDataTag extends \Topxia\WebBundle\Extensions\DataTag\BaseDataTag
{
    protected function createService($name)
    {
        return $this->getServiceKernel()->createService($name);
    }
}