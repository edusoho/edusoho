<?php

namespace AppBundle\Extensions\DataTag;

use Biz\MultiClass\Service\MultiClassService;

class MultiClassDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        return $this->getMultiClassService()->getMultiClassByCourseId($arguments['courseId']);
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->getServiceKernel()->getBiz()->service('MultiClass:MultiClassService');
    }
}
