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
        $setting = $this->getSettingService()->get('anti_fraud_reminder', []);

        if (empty($setting['enable'])) {
            return ['result' => true, 'message' => 'success'];
        }
        /**
         * 是否是所有用户可见  是=》                上次提醒时间=0    是=》  弹框
         *                                                      否=》  进入App时间-上次提醒时间 >=提醒频率  是=》弹框
         *                                                                                            否=》不弹框
         *                  否=》是否为老用户  是=》上次提醒时间=0
         *                                  否=》不弹框
         */
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

        //非全体用户 非老用户
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
        //上次提醒时间!=0 且 进入App时间-上次提醒时间 < 提醒频率
        if (!empty($antiFraudRemind['lastRemindTime']) && (time() - $antiFraudRemind['lastRemindTime'] < $setting['reminder_frequency'] * 86400)) {
            return ['result' => true, 'message' => 'success'];
        }

        $this->getAntiFraudRemindService()->updateLastRemindTime(['id' => $antiFraudRemind['id']], ['lastRemindTime' => time()]);

        $message = [
            'content' => $this->trans('admin.anti_fraud_reminder.tips'),
            'url' => '',
        ];

        return ['result' => false, 'message' => $message];
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
