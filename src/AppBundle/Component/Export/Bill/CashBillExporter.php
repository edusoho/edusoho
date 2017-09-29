<?php

namespace AppBundle\Component\Export\Bill;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class CashBillExporter extends Exporter
{
    public function getTitles()
    {
        return array(
            '流水号',
            '名称',
            '用户名',
            '成交时间',
            '金额',
            '支付方式',
            '支付平台流水号',
            '邮箱',
            '联系电话',
        );
    }

    public function getContent($start, $limit)
    {
        $cashes = $this->getAccountProxyService()->searchUserCashflows(
            $this->conditions,
            array('id' => 'DESC'),
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($cashes, 'buyer_id');
        $userIds = $this->getUserService()->findUsersByIds($userIds);
        $datas = array();
        
        foreach($cashes as $cash) {
            $content = array();
            $user = empty($users[$cash['buyer_id']]) ? array('nickname' => '--', 'email' => '--', 'verifiedMobile' => '--') : $users[$cash['buyer_id']];
            $content[] = $cash['sn'];
            $content[] = $cash['title'];
            $content[] = $user['nickname'];
            $content[] = $cash['created_time'];
            $content[] = $cash['amount'];
            $content[] = '支付方式';
            $content[] = $cash['trade_sn'];
            $content[] = $user['email'];
            $content[] = $user['verifiedMobile'];
            $datas[] = $content;
        }

        return $datas;
    }

    public function canExport()
    {
        $user = $this->getUser();

        if ($user->hasPermission('admin_bills')) {
            return true;
        }

        return false;
    }

    public function getCount()
    {
        return $this->getAccountProxyService()->countUserCashflows($this->conditions);
    }

    public function buildCondition($conditions)
    {
        $conditions['amount_type'] = 'money';
        $conditions['user_id'] = 0;

        return  $conditions;
    }

    protected function getAccountProxyService()
    {
        return $this->getBiz()->service('Account:AccountProxyService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}