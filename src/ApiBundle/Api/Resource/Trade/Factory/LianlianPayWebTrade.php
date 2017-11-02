<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class LianlianPayWebTrade extends BaseTrade
{
    protected $payment = 'lianlianpay';

    protected $platformType = 'Web';

    public function getCustomFields($params)
    {
        $user = $this->getUser();
        return array(
            'attach' => array(
                'user_id' => $user['id'],
                'user_created_time' => $user['createdTime'],
                'identify_user_id' => $this->getIdentify().'_'.$user['id'],
            )
        );
    }

    protected function getIdentify()
    {
        $identify = $this->getSettingService()->get('llpay_identify');
        if (empty($identify)) {
            $identify = substr(md5(uniqid()), 0, 12);
            $this->getSettingService()->set('llpay_identify', $identify);
        }

        return $identify;
    }
}