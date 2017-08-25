<?php

namespace Biz\CloudPlatform\QueueJob;

use Biz\CloudPlatform\Service\SearchService;
use Codeages\Biz\Framework\Queue\AbstractJob;

class SearchJob extends AbstractJob
{
    public function execute()
    {
        $context = $this->getBody();
        $type = $context['type'];
        $args = $context['args'];
        if (!in_array($type, array('delete', 'update'))) {
            return;
        }

        if ($type == 'update') {
            $this->getSearchService()->notifyUpdate($args);
        }

        if ($type == 'delete') {
            $this->getSearchService()->notifyDelete($args);
        }
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->biz->service('CloudPlatform:SearchService');
    }
}
