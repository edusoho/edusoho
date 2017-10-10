<?php

namespace AppBundle\Component\Export\Bill;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class CashBillExporter extends Exporter
{
    public function getTitles()
    {
        return array(
            'cashflow.sn',
            'cashflow.title',
            'cashflow.order_sn',
            'cashflow.user_name',
            'cashflow.created_time',
            'cashflow.amount',
            'cashflow.platform',
            'cashflow.trade_sn',
            'cashflow.user_truename',
            'cashflow.user_email',
            'cashflow.user_mobile',
        );
    }

    public function getContent($start, $limit)
    {
        $payment = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('payment');
        $cashes = $this->getAccountProxyService()->searchUserCashflows(
            $this->conditions,
            array('id' => 'DESC'),
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($cashes, 'buyer_id');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        $datas = array();

        foreach ($cashes as $cash) {
            $content = array();
            $user = empty($users[$cash['buyer_id']]) ? array('nickname' => '--', 'email' => '--', 'verifiedMobile' => '--') : $users[$cash['buyer_id']];
            $profile = empty($profiles[$cash['buyer_id']]) ? array('truename' => '--') : $profiles[$cash['buyer_id']];

            //系统生成的邮箱不显示
            if (strpos($user['email'], '@edusoho.net') !== false) {
                $user['email'] = '--';
            }
            $content[] = $cash['sn'];
            $content[] = $cash['title'];
            $content[] = empty($cash['order_sn']) ? '--' : $cash['order_sn'];
            $content[] = $user['nickname'];
            $content[] = date('Y-n-d H:i:s', $cash['created_time']);
            $content[] = $cash['amount'] / 100;
            if ($cash['type'] == 'outflow' && $cash['amount_type'] == 'money') {
                //网校支出
                $content[] = $this->container->get('translator')->trans('order.payment_pattern.school');
            } elseif ($cash['type'] == 'inflow' && $cash['amount_type'] == 'coin') {
                //用户余额支付
                $content[] = $this->container->get('translator')->trans('order.payment_pattern.balance');
            } else {
                $content[] = empty($payment[$cash['platform']]) ? '--' : $payment[$cash['platform']];
            }
            $content[] = $cash['trade_sn'];
            $content[] = $profile['truename'];
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
