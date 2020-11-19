<?php

namespace Biz\Visualization\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Biz\Visualization\Dao\UserActivityLearnFlowDao;
use Biz\Visualization\Service\LearnControlService;

class LearnControlServiceImpl extends BaseService implements LearnControlService
{
    const MULTIPLE_LEARN_MODE_KICK_PREVIOUS = 'kick_previous';

    const MULTIPLE_LEARN_MODE_REJECT_CURRENT = 'reject_current';

    const MULTI_LEARN_DENY_REASON_KICK_PREVIOUS = 'kick_previous';

    const MULTI_LEARN_DENY_REASON_REJECT_CURRENT = 'reject_current';

    const MULTI_LEARN_DENY_REASON_FLOW_NOT_EXIST = 'flow_not_exist';

    public function getUserLastLearnRecord($userId)
    {
        return $this->getActivityLearnRecordDao()->getUserLastLearnRecord($userId);
    }

    public function getUserLastLearnRecordBySign($userId, $sign)
    {
        return $this->getActivityLearnRecordDao()->getUserLastLearnRecordBySign($userId, $sign);
    }

    public function getUserLatestActiveFlow($userId)
    {
        return $this->getUserActivityLearnFlowDao()->getUserLatestActiveFlow($userId);
    }

    /**
     * @param $userId
     * @param $sign
     * @param false $reActive 是否需要重新使其变成活跃状态，需要标记其他为非活跃
     *
     * @return array
     */
    public function checkActive($userId, $sign, $reActive = false)
    {
        $setting = $this->getMultipleLearnSetting();
        //允许多开，都是活跃的
        if (1 === $setting['multiple_learn_enable']) {
            return [true, ''];
        }
        //sign对应的不存在
        $existFlow = $this->getUserActivityLearnFlowDao()->getByUserIdAndSign($userId, $sign);
        if (empty($existFlow)) {
            return [false, self::MULTI_LEARN_DENY_REASON_FLOW_NOT_EXIST];
        }

        //允许后踢前，并且要踢掉前面的$reActive === true，则执行更新流水
        if ((self::MULTIPLE_LEARN_MODE_KICK_PREVIOUS === $setting['multiple_learn_kick_mode']) && $reActive) {
            $this->freshFlow($userId, $sign);
        }
        //更新后，获取flow，sign对应的flow状态是否活跃
        $flow = $this->getUserActivityLearnFlowDao()->getByUserIdAndSign($userId, $sign);
        if (1 === (int) $flow['active']) {
            return [true, ''];
        }

        //当前sign不活跃
        $reason = self::MULTIPLE_LEARN_MODE_KICK_PREVIOUS === $setting['multiple_learn_kick_mode']
            ? self::MULTIPLE_LEARN_MODE_KICK_PREVIOUS : self::MULTIPLE_LEARN_MODE_REJECT_CURRENT;

        return [false, $reason];
    }

    /**
     * @param $userId
     * @param string $invalidSign
     *
     * @return array|bool
     */
    public function checkCreateNewFlow($userId, $invalidSign = '')
    {
        $setting = $this->getMultipleLearnSetting();
        //如果允许多开，则直接允许新建
        if (1 === $setting['multiple_learn_enable']) {
            return [true, ''];
        }

        //如果存在前端传过来的需要标记为无效的请求，则标记为无效
        if ($invalidSign) {
            $flow = $this->getUserActivityLearnFlowDao()->getByUserIdAndSign($userId, $invalidSign);
            $this->getUserActivityLearnFlowDao()->update($flow['id'], ['active' => 0]);
        }

        //如果不允许后开，则当没活跃的记录存在返回false
        if (self::MULTIPLE_LEARN_MODE_REJECT_CURRENT === $setting['multiple_learn_kick_mode']) {
            $latestFlow = $this->getUserLatestActiveFlow($userId);
            if (!$latestFlow || time() - $latestFlow['lastLearnTime'] > 80) {
                return [true, ''];
            }

            return [false, self::MULTI_LEARN_DENY_REASON_REJECT_CURRENT];
        }

        return [true, ''];
    }

    public function freshFlow($userId, $sign)
    {
        $flow = $this->getUserActivityLearnFlowDao()->getByUserIdAndSign($userId, $sign);
        $this->getUserActivityLearnFlowDao()->update($flow['id'], ['active' => 1]);
        $this->getUserActivityLearnFlowDao()->setUserOtherFlowUnActive($userId, $sign);
    }

    protected function getMultipleLearnSetting()
    {
        $multiLearnSetting = $this->getSettingService()->get('taskPlayMultiple', []);

        return [
            'multiple_learn_enable' => !isset($multiLearnSetting['multiple_learn_enable']) ? 1 : (int) $multiLearnSetting['multiple_learn_enable'],
            'multiple_learn_kick_mode' => !isset($multiLearnSetting['multiple_learn_kick_mode']) ? 'kick_previous' : $multiLearnSetting['multiple_learn_kick_mode'],
        ];
    }

    /**
     * @return UserActivityLearnFlowDao
     */
    protected function getUserActivityLearnFlowDao()
    {
        return $this->createDao('Visualization:UserActivityLearnFlowDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return ActivityLearnRecordDao
     */
    protected function getActivityLearnRecordDao()
    {
        return $this->createDao(['Visualization:ActivityLearnRecordDao']);
    }
}
