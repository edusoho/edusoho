<?php

namespace Biz\MultiClass\Job;

use Biz\MultiClass\Service\MultiClassService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloneMultiClassJob extends AbstractJob
{
    public function execute()
    {
        $multiClassId = $this->args['multiClassId'];
        $this->getMultiClassService()->cloneMultiClass($multiClassId);
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->biz->service('MultiClass:MultiClassService');
    }
}
