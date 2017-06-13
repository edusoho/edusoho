<?php

namespace Biz\RewardPoint\Processor;

class CommonAcquireRewardPoint extends RewardPoint
{
    public function circulatingRewardPoint($params)
    {
        $result = $this->verifySettingEnable($params['way']);

        if ($result) {
            $result = $this->canCirculating($params);

            if ($result) {
                $this->circulatingCommonRewardPoint($params);
            }
        }
    }

    public function verifySettingEnable($param = null)
    {
        $settings = $this->getSettingService()->get('reward_point', array());
        $result = false;
        if (!empty($settings)) {
            if (isset($settings['enable']) && $settings['enable'] == 1) {
                if (isset($settings[$param]['enable']) && $settings[$param]['enable'] == 1) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    public function canCirculating($params)
    {
        $result = false;
        $user = $this->getUser();
        if (isset($params['userId'])) {
            $user = $this->getUserService()->getUser($params['userId']);
        }

        $settings = $this->getSettingService()->get('reward_point', array());

        if (empty($settings[$params['way']]['daily_limit'])) {
            $flow = $this->getAccountFlowService()->getInflowByUserIdAndTarget($user['id'], $params['targetId'], $params['targetType']);
            if (empty($flow)) {
                $result = true;
            }
        } else {
            if ($settings[$params['way']]['daily_limit'] <= 0) {
                $result = true;
            } else {
                $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $endTime = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
                $inflow = $this->getAccountFlowService()->sumInflowByUserIdAndWayAndTime($user['id'], $params['way'], $startTime, $endTime);

                if ($inflow < $settings[$params['way']]['daily_limit']) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    protected function circulatingCommonRewardPoint($params)
    {
        $user = $this->getUser();
        if (isset($params['userId'])) {
            $user = $this->getUserService()->getUser($params['userId']);
        }
        $settings = $this->getSettingService()->get('reward_point', array());
        $amount = $settings[$params['way']]['amount'];

        $this->waveRewardPoint($user['id'], $amount);

        $flow = array(
            'userId' => $user['id'],
            'type' => 'inflow',
            'amount' => $amount,
            'targetId' => $params['targetId'],
            'targetType' => $params['targetType'],
            'way' => $params['way'],
            'operator' => (isset($params['userId'])) ? $this->getUser()['id'] : 0,
        );

        $this->keepFlow($flow);
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
