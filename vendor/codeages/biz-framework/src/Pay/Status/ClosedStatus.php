<?php

namespace Codeages\Biz\Framework\Pay\Status;

class ClosedStatus extends AbstractStatus
{
    const NAME = 'closed';

    public function getPriorStatus()
    {
        return array(ClosingStatus::NAME);
    }

}