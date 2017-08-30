<?php

namespace Biz\CloudPlatform\QueueJob;

use Biz\CloudPlatform\Service\SearchService;
use Codeages\Biz\Framework\Queue\AbstractJob;
use Codeages\Biz\Framework\Queue\Service\QueueService;

class SearchJob extends AbstractJob
{
    public function execute()
    {
        $context = $this->getBody();
        $type = $context['type'];
        $args = $context['args'];
        if (!in_array($type, array('delete', 'update'))) {
            return array(
                self::FAILED,
                "只支持delete,update两种类型，你的类型是{$type}",
            );
        }

        try {
            if ($type == 'update') {
                $result = $this->getSearchService()->notifyUpdate($args);
            }

            if ($type == 'delete') {
                $result = $this->getSearchService()->notifyDelete($args);
            }
            if (!empty($result['error'])) {
                return array(
                    self::FAILED,
                    $result['error'],
                );
            }
        } catch (\Exception $e) {
            return array(
                self::FAILED,
                $e->getMessage(),
            );
        }
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->biz->service('CloudPlatform:SearchService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }
}
