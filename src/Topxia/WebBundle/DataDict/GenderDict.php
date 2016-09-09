<?php
namespace Topxia\WebBundle\DataDict;

use Topxia\Service\Common\ServiceKernel;

class GenderDict implements DataDictInterface
{
    public function getDict() {
        return array(
            'male' => $this->getServiceKernel()->trans('男'),
            'female' => $this->getServiceKernel()->trans('女'),
        );
    }


    public function getRenderedDict() {
        return $this->getDict();
    }

    public function getGroupedDict() {
        return $this->getDict();
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}