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

        $isOrderUser = false;
        if (empty($setting['all_users_visible'])) {
            $orders = $this->getOrderService()->searchOrders(['user_id' => $user['id']], [], 0, PHP_INT_MAX, ['price_amount']);
            if (empty($orders)) {
                $isOrderUser = false;
            }
            $priceAmount = array_sum(array_column($orders, 'price_amount'));
            if ($priceAmount >= 0.01) {
                $isOrderUser = true;
            }
        }

        if ('1' != $setting['all_users_visible'] && !$isOrderUser) {
            return ['result' => true, 'message' => 'success'];
        }

        $antiFraudRemind = $this->getAntiFraudRemindService()->getByUserId($user['id']);
        if (empty($antiFraudRemind)) {
            $antiFraudRemind = $this->getAntiFraudRemindService()->creatAntiFraudRemind([
                'userId' => $user['id'],
                'lastRemindTime' => '0',
            ]);
        }

        if (!empty($antiFraudRemind['lastRemindTime']) &&
            (time() - $antiFraudRemind['lastRemindTime'] < $setting['reminder_frequency'] * 86400)) {
            return ['result' => true, 'message' => 'success'];
        }

        $this->getAntiFraudRemindService()->updateLastRemindTime(['id' => $antiFraudRemind['id']], ['lastRemindTime' => time()]);

        return [
            'result' => false,
            'title' => trim($this->trans('admin.anti_fraud_reminder.tips.title'), '【】'),
            'content' => $this->trans('admin.anti_fraud_reminder.tips.content'),
            'button' => $this->trans('admin.anti_fraud_reminder.tips.detail'),
            'url' => '',
        ];
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
