<?php

namespace Biz\Account\Service\Impl;

use Biz\Account\Service\AccountProxyService;
use Codeages\Biz\Framework\Pay\Service\Impl\AccountServiceImpl;

class AccountProxyServiceImpl extends AccountServiceImpl implements AccountProxyService
{
    public function countUsersByConditions($conditions)
    {
        $conditions = $this->_prepareConditions($conditions);
        return parent::countUsersByConditions($conditions);
    }

    public function searchUserIdsGroupByUserIdOrderByBalance($conditions, $sort, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);
        return parent::searchUserIdsGroupByUserIdOrderByBalance($conditions, $sort, $start, $limit);
    }

    protected function _prepareConditions($conditions)
    {
        if (isset($conditions['timeType'])) {
            switch ($conditions['timeType']) {
                case 'oneWeek':
                    $conditions['created_time_GTE'] = time() - 7 * 3600 * 24;
                    break;
                case 'oneMonth':
                    $conditions['created_time_GTE'] = time() - 30 * 3600 * 24;
                    break;
                case 'threeMonths':
                    $conditions['created_time_GTE'] = time() - 90 * 3600 * 24;
                    break;
                case 'all':
                    $conditions['created_time_GTE'] = 0;
                    break;
                default:
                    break;
            }

            unset($conditions['timeType']);
        }

        return $conditions;

    }

}