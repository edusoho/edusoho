<?php

namespace Biz\RewardPoint\Processor;

class CommonAcquireRewardPoint extends AcquireRewardPoint
{
    public function circulatingRewardPoint($type)
    {
        $settings = $this->getSettingService()->get('reward_point', array());
        $result = $this->verifySettingEnable($settings, $type);

        if ($result) {
            $rule = $settings['common_rule'][$type];
            $user = $this->getUser();
            $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endTime = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $inflow = $this->getAccountFlowService()->sumInflowByUserIdAndWayAndTime($user['id'], $type, $startTime, $endTime);

            if ($rule['daily_limit'] <= 0) {
                $this->waveRewardPoint($user['id'], $rule['value']);
            } else {
                if ($inflow < $rule['daily_limit']) {
                    $this->waveRewardPoint($user['id'], $rule['value']);
                }
            }
        }
    }

    private function verifySettingEnable($settings, $type)
    {
        $result = false;
        if (!empty($settings)) {
            if (isset($settings['enable']) && $settings['enable'] == 1) {
                if (isset($settings['common_rule'][$type]['enable']) && $settings['common_rule'][$type]['enable'] == 1) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
