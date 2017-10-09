<?php

namespace AppBundle\Component\Export\Bill;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Bill\CashBillExporter;

class CoinBillExporter extends CashBillExporter
{
    // public function getContent($start, $limit)
    // {
    //     $payment = $this->container->get('codeages_plugin.dict_twig_extension')->getDict('payment');
    //     $cashes = $this->getAccountProxyService()->searchUserCashflows(
    //         $this->conditions,
    //         array('created_time' => 'DESC'),
    //         $start,
    //         $limit
    //     );

    //     $userIds = ArrayToolkit::column($cashes, 'buyer_id');
    //     $users = $this->getUserService()->findUsersByIds($userIds);

    //     $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

    //     $datas = array();
        
    //     foreach($cashes as $cash) {
    //         $content = array();
    //         $user = empty($users[$cash['buyer_id']]) ? array('nickname' => '--', 'email' => '--', 'verifiedMobile' => '--') : $users[$cash['buyer_id']];
    //         $profile = empty($profiles[$cash['buyer_id']]) ? array('truename' => '--') : $profiles[$cash['buyer_id']];
            
    //         //系统生成的邮箱不显示
    //         if (strpos($user['email'], '@edusoho.net') !== false) {
    //             $user['email'] = '--';
    //         }
    //         $content[] = $cash['sn'];
    //         $content[] = $cash['title'];
    //         $content[] = $user['nickname'];
    //         $content[] = date('Y-n-d H:i:s', $cash['created_time']);
    //         $content[] = $cash['amount'];
    //         $content[] = empty($payment[$cash['platform']]) ? '--' : $payment[$cash['platform']];
    //         $content[] = $cash['trade_sn'];
    //         $content[] = $profile['truename'];
    //         $content[] = $user['email'];
    //         $content[] = $user['verifiedMobile'];
    //         $datas[] = $content;
    //     }

    //     return $datas;
    // }

    public function buildCondition($conditions)
    {
        $conditions['except_user_id'] = 0;
        $conditions['amount_type'] = 'coin';

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