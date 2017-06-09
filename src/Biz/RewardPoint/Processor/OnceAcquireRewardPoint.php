<?php

namespace Biz\RewardPoint\Processor;

class OnceAcquireRewardPoint extends RewardPoint
{
    public function circulatingRewardPoint($params)
    {
        $settings = $this->getSettingService()->get('reward_point', array());
        $result = $this->verifySettingEnable($settings, $params['way']);

        if ($result) {
            $rule = $settings[$params['way']];
            $user = $this->getUser();

            $flow = $this->getAccountFlowService()->getInFlowByWayAndTarget($params['way'], $params['targetId'], $params['targetType']);

            if (!empty($flow)) {
                $this->waveRewardPoint($user['id'], $rule['amount']);
                $flow = array(
                    'userId' => $user['id'],

                );
            }
        }
    }

    private function verifySettingEnable($settings, $way)
    {
        $result = false;
        if (!empty($settings)) {
            if (isset($settings['enable']) && $settings['enable'] == 1) {
                if (isset($settings[$way]['enable']) && $settings[$way]['enable'] == 1) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}