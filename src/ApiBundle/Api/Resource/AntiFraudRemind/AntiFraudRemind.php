<?php

namespace ApiBundle\Api\Resource\AntiFraudRemind;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AntiFraudRemind\Service\AntiFraudRemindService;

class AntiFraudRemind extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $setting = $this->getSettingService()->get('anti_fraud_reminder', [
            'enable' => 1,
            'all_users_visible' => 1,
            'reminder_frequency' => 1,
        ]);

        if (empty($setting['enable'])) {
            return ['result' => true, 'message' => 'success'];
        }

        if (!$this->checkUserNeedReminding($setting, $user['id'])) {
            return ['result' => true, 'message' => 'success'];
        }

        if (!$this->checkReminderFrequency($setting, $user['id'])) {
            return ['result' => true, 'message' => 'success'];
        }

        $this->markReminded($user['id']);

        return [
            'result' => false,
            'title' => trim($this->trans('admin.anti_fraud_reminder.tips.title'), '【】'),
            'content' => $this->trans('admin.anti_fraud_reminder.tips.content'),
            'button' => $this->trans('admin.anti_fraud_reminder.tips.detail'),
            'url' => '',
        ];
    }

    /**
     * @param $setting
     * @param $userId
     */
    private function checkUserNeedReminding($setting, $userId): bool
    {
        if (!empty($setting['all_users_visible'])) {
            return true;
        }

        if ($this->checkRegularCustomer($userId)) {
            return true;
        }

        return false;
    }

    /**
     * 判断是否是老用户，有过消费则为老用户
     *
     * @param $userId
     */
    private function checkRegularCustomer($userId): bool
    {
        $orders = $this->getOrderService()->searchOrders(['user_id' => $userId], [], 0, PHP_INT_MAX, ['pay_amount']);
        if (empty($orders)) {
            return false;
        }

        return array_sum(array_column($orders, 'pay_amount')) >= 0.01;
    }

    /**
     * @param $setting
     * @param $userId
     *
     * @return bool 需要提醒
     */
    private function checkReminderFrequency($setting, $userId): bool
    {
        $antiFraudRemind = $this->getAntiFraudRemindService()->getByUserId($userId);
        if (empty($antiFraudRemind['lastRemindTime'])) {
            return true;
        }

        return time() - $antiFraudRemind['lastRemindTime'] >= $setting['reminder_frequency'] * 86400;
    }

    /**
     * @param $userId
     *
     * @return void
     */
    private function markReminded($userId)
    {
        $antiFraudRemind = $this->getAntiFraudRemindService()->getByUserId($userId);
        if (!empty($antiFraudRemind)) {
            $this->getAntiFraudRemindService()->updateLastRemindTime(['id' => $antiFraudRemind['id']], ['lastRemindTime' => time()]);

            return;
        }
        $this->getAntiFraudRemindService()->creatAntiFraudRemind([
            'userId' => $userId,
            'lastRemindTime' => '0',
        ]);
    }

    protected function getOrderService()
    {
        return $this->service('Order:OrderService');
    }

    /**
     * @return AntiFraudRemindService
     */
    protected function getAntiFraudRemindService()
    {
        return $this->service('AntiFraudRemind:AntiFraudRemindService');
    }
}
