<?php

namespace Biz\RewardPoint\Processor;

class CommonAcquireRewardPoint extends RewardPoint
{
    public function canReward($params)
    {
        return $this->verifySettingEnable($params['way']) && $this->canWave($params);
    }

    public function generateFlow($params)
    {
        $flow = array(
            'userId' => $params['userId'],
            'type' => 'inflow',
            'amount' => $this->getAmount($params['way']),
            'targetId' => $params['targetId'],
            'targetType' => $params['targetType'],
            'way' => $params['way'],
            'operator' => $this->getUser()->getId(),
        );

        return $flow;
    }

    protected function verifySettingEnable($param)
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

    protected function canWave($params)
    {
        $result = false;

        $settings = $this->getSettingService()->get('reward_point', array());

        if (isset($settings[$params['way']]['daily_limit'])) {
            $result = $this->decideCanWaveWithDailyLimit($params);
        } else {
            $result = $this->decideCanWaveNoDailyLimit($params);
        }

        return $result;
    }

    protected function decideCanWaveNoDailyLimit($params)
    {
        $result = false;
        $settings = $this->getSettingService()->get('reward_point', array());
        $user = $this->getUserService()->getUser($params['userId']);
        if ($params['way'] == 'daily_login') {
            $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endTime = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $inflow = $this->getAccountFlowService()->sumInflowByUserIdAndWayAndTime($user['id'], $params['way'], $startTime, $endTime);

            if ($inflow < $settings[$params['way']]['amount']) {
                $result = true;
            }
        } else {
            $flow = $this->getAccountFlowService()->getInflowByUserIdAndTarget($user['id'], $params['targetId'], $params['targetType']);
            if (empty($flow)) {
                $result = true;
            }
        }

        if ($settings[$params['way']]['amount'] == 0) {
            $result = false;
        }

        return $result;
    }

    protected function decideCanWaveWithDailyLimit($params)
    {
        $result = false;
        $settings = $this->getSettingService()->get('reward_point', array());
        if ($settings[$params['way']]['daily_limit'] <= 0) {
            $result = true;
        } else {
            $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endTime = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $inflow = $this->getAccountFlowService()->sumInflowByUserIdAndWayAndTime($params['userId'], $params['way'], $startTime, $endTime);

            if ($inflow < $settings[$params['way']]['daily_limit']) {
                $result = true;
            }
        }

        if ($settings[$params['way']]['amount'] == 0) {
            $result = false;
        }

        return $result;
    }

    protected function getAmount($way)
    {
        $settings = $this->getSettingService()->get('reward_point', array());

        return $settings[$way]['amount'];
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
