<?php

namespace Biz\RewardPoint\Processor;

class DailyAcquireLimitRewardPoint extends AcquireRewardPoint
{
    public function circulatingRewardPoint($params)
    {
        $settings = $this->getSettingService()->get('reward_point', array());
        $result = $this->verifySettingEnable($settings, $params['way']);

        if ($result) {
            $rule = $settings[$params['way']];
            $user = $this->getUser();
            $startTime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endTime = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $inflow = $this->getAccountFlowService()->sumInflowByUserIdAndWayAndTime($user['id'], $params['way'], $startTime, $endTime);

            if ($rule['daily_limit'] <= 0) {
                $this->waveRewardPoint($user['id'], $rule['amount']);
            } else {
                if ($inflow < $rule['daily_limit']) {
                    $this->waveRewardPoint($user['id'], $rule['amount']);
                }
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
