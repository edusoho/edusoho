<?php

namespace Biz\Account\Service\Impl;

use Biz\Account\Service\AccountProxyService;
use Codeages\Biz\Pay\Service\Impl\AccountServiceImpl;

class AccountProxyServiceImpl extends AccountServiceImpl implements AccountProxyService
{
    public function countUsersByConditions($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return parent::countUsersByConditions($conditions);
    }

    public function countCashflows($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return parent::countCashflows($conditions);
    }

    public function searchCashflows($conditions, $orderBy, $start, $limit, $columns = array())
    {
        $conditions = $this->prepareConditions($conditions);

        return parent::searchCashflows($conditions, $orderBy, $start, $limit, $columns);
    }

    public function sumColumnByConditions($column, $conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return parent::sumColumnByConditions($column, $conditions);
    }

    public function prepareConditions($conditions)
    {
        if (!empty($conditions['startTime'])) {
            $conditions['created_time_GTE'] = strtotime($conditions['startTime']);
            unset($conditions['startTime']);
        }
        if (!empty($conditions['endTime'])) {
            $conditions['created_time_LT'] = strtotime($conditions['endTime']);
            unset($conditions['endTime']);
        }

        if (!empty($conditions['keyword']) && !empty($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = trim($conditions['keyword']);
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (!empty($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['user_id'] = empty($user) ? -1 : $user['id'];
            unset($conditions['nickname']);
        }

        if (!empty($conditions['buyerNickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyerNickname']);
            $conditions['buyer_id'] = empty($user) ? -1 : $user['id'];
            unset($conditions['buyerNickname']);
        }

        if (!empty($conditions['platform_sn'])) {
            $trade = $this->getPayService()->getTradeByPlatformSn($conditions['platform_sn']);
            $conditions['trade_sn'] = empty($trade) ? '0' : $trade['trade_sn'];
            unset($conditions['platform_sn']);
        }

        if (!empty($conditions['platform']) && 'none' == $conditions['platform']) {
            $conditions['amount_GT'] = 0;
            $conditions['type'] = 'outflow';
            $conditions['action'] = 'refund';
            unset($conditions['platform']);
        }

        return $conditions;
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }
}
