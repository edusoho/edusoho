<?php

namespace Biz\CloudPlatform\QueueJob;

use Codeages\Biz\Framework\Queue\AbstractJob;

class SearchJob extends AbstractJob
{
    public function execute()
    {
        $context = $this->getBody();
        //@TODO

    }

    protected function getSearchService()
    {
        return $this->biz->service('CloudPlatform:SearchService');
    }
}
