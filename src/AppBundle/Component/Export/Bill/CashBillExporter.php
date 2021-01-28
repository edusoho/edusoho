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
            'cashflow.trade_sn',
            'cashflow.user_name',
            'cashflow.created_time',
            'cashflow.amount',
            'cashflow.platform',
            'cashflow.platform_sn',
            'cashflow.user_truename',
            'cashflow.user_email',
            'cashflow.user_mobile',
        );
    }

    public function getContent($start, $limit)
    {
        $payment = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('payment');
        $cashes = $this->getAccountProxyService()->searchCashflows(
            $this->conditions,
            array('id' => 'DESC'),
            $start,
            $limit
        );

        $tradeSns = ArrayToolkit::column($cashes, 'trade_sn');
        $trades = $this->getPayService()->findTradesByTradeSn($tradeSns);
        $trades = ArrayToolkit::index($trades, 'trade_sn');

        $userIds = ArrayToolkit::column($cashes, 'buyer_id');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        $datas = array();

        foreach ($cashes as $cash) {
            $content = array();
            $trade = empty($trades[$cash['trade_sn']]) ? array('platform_sn' => '--', 'trade_sn' => '--') : $trades[$cash['trade_sn']];
            if ('outflow' == $cash['type'] && 'money' == $cash['amount_type']) {
                //网校支出
                $amountMark = '-';
                $paymentText = $this->container->get('translator')->trans('order.payment_pattern.school');
            } elseif ('inflow' == $cash['type'] && 'coin' == $cash['amount_type']) {
                //用户余额支付
                $amountMark = '+';
                $paymentText = $this->container->get('translator')->trans('order.payment_pattern.balance');
            } else {
                if ('coin' == $cash['amount_type']) {
                    $amountMark = '-';
                } else {
                    $amountMark = '+';
                }

                $paymentText = empty($payment[$cash['platform']]) ? '--' : $payment[$cash['platform']];
            }

            $user = empty($users[$cash['buyer_id']]) ? array('nickname' => '--', 'email' => '--', 'verifiedMobile' => '--') : $users[$cash['buyer_id']];
            $profile = empty($profiles[$cash['buyer_id']]) ? array('truename' => '--') : $profiles[$cash['buyer_id']];

            //系统生成的邮箱不显示
            if (false !== strpos($user['email'], '@edusoho.net')) {
                $user['email'] = '--';
            }
            $content[] = $cash['sn']."\t";
            $content[] = $cash['title'];
            $content[] = empty($cash['order_sn']) ? '--' : $cash['order_sn']."\t";
            $content[] = $cash['trade_sn']."\t";
            $content[] = $user['nickname'];
            $content[] = date('Y-n-d H:i:s', $cash['created_time']);
            $content[] = $amountMark.$cash['amount'] / 100;
            if ('money' == $cash['amount_type']) {
                $content[] = $paymentText;
                $content[] = $trade['platform_sn']."\t";
            }
            $content[] = $profile['truename'];
            $content[] = $user['email'];
            $content[] = $profile['mobile']."\t";
            $datas[] = $content;
        }

        return $datas;
    }

    public function canExport()
    {
        $user = $this->getUser();

        if ($user->hasPermission('admin_bills') || $user->hasPermission('admin_v2_bills')) {
            return true;
        }

        return false;
    }

    public function getCount()
    {
        return $this->getAccountProxyService()->countCashflows($this->conditions);
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

    protected function getPayService()
    {
        return $this->getBiz()->service('Pay:PayService');
    }
}
