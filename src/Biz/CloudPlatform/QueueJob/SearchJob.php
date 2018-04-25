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
            return array(
                self::FAILED,
                "只支持delete,update两种类型，你的类型是{$type}",
            );
        }

        try {
            if ('update' == $type) {
                $result = $this->getSearchService()->notifyUpdate($args);
            }

            if ('delete' == $type) {
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
}
