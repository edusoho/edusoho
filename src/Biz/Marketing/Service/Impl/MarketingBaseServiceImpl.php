<?php

namespace Biz\Marketing\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Marketing\Dao\MarketingOrderDao;
use Biz\User\CurrentUser;
use Biz\Role\Util\PermissionBuilder;
use Biz\Marketing\Service\MarketingService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;

abstract class MarketingBaseServiceImpl extends BaseService implements MarketingService
{
    /**
     * @param $postData  (价格单位均为分)
     * 课程:
     * {
     *      "activity_id": "123815",
     *      "activity_name": "打折活动A",
     *      "client_ip": "127.0.0.1",
     *      "deduct": {
     *          "id":"2863",
     *          "order_id":"2874",
     *          "detail":"打折活动详情",
     *          "item_id":"2873",
     *          "deduct_type": "cut",
     *          "deduct_id": "129555",
     *          "deduct_amount": "10999",
     *          "status": "paid",
     *          "user_id": "10000",
     *          "seller_id": "1",
     *          "snapshot":[],
     *          "created_time": "1511948304",
     *          "updated_time":"1511948322"
     *      },
     *      "mobile": "18812345678",
     *      "nickname":"Drrrug.",
     *      "order_id":"2874",
     *      "order_pay_amount":"1",
     *      "order_pay_time": "1517401609",
     *      "order_price_amount":"11000",
     *      "target_id":"19",
     *      "target_type":"course",
     *      "user_id":"10000"
     *  }
     */
    public function join($postData)
    {
        $logger = $this->biz['logger'];
        $logger->info(json_encode($postData));
        if (empty($postData['order_id'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        list($isNew, $user, $password) = $this->createUserIfNeed($postData, $logger);

        $logger->info($this->getPreparedDebugInfo($user));
        $orderInfo = array(
            'marketingOrderId' => $postData['order_id'],
            'marketingOrderPriceAmount' => $postData['order_price_amount'] / 100,  //原价和现价 都需要除以 100，deduct不需要除以 100
            'marketingOrderPayAmount' => $postData['order_pay_amount'] / 100,
            'marketingActivityId' => $postData['activity_id'],
            'marketingActivityName' => $postData['activity_name'],
            'deducts' => $this->getUserOrderDeduct($user['id'], $postData['deduct']),
            'pay_time' => empty($postData['order_pay_time']) ? 0 : $postData['order_pay_time'],
        );
        $target = array(
            'type' => $postData['target_type'],
            'id' => $postData['target_id'],
        );

        $hasJoined = false;
        try {
            list($product, $member, $order) = $this->makeUserJoin($user['id'], $target['id'], $orderInfo);
        } catch (\Exception $e) {
            if ($this->getDuplicateJoinCode() != $e->getCode()) {
                $this->createNewException($e);
            }
            $hasJoined = true;
            list($product, $member, $order) = $this->tryCreateMarketingOrder($user['id'], $target['id'], $orderInfo);
        }

        $logMsg = $this->getFinishedInfo($user, $product, $member, $order, $hasJoined);
        $logger->info($logMsg);

        $response = array();
        $response['code'] = 'success';
        $response['is_new'] = $isNew;
        $response['user_id'] = $user['id'];
        if ($isNew) {
            $response['password'] = $password;
        }

        $response['msg'] = $logMsg;

        return $response;
    }

    abstract protected function joinTarget($targetId, $userId, $data);

    /**
     * @return "获取目标产品信息";
     */
    abstract protected function getProduct($targetId);

    /**
     * @return "获取目标成员信息";
     */
    abstract protected function getMember($targetId, $userId);

    /**
     * @return "发现学员已加入后补建的微营销订单信息";
     */
    abstract protected function createMarketingOrder($targetId, $userId, $data);

    /**
     * @return "获取产品重复加入code值";
     */
    abstract protected function getDuplicateJoinCode();

    /**
     * @return 如 "准备把用户,{$user['id']}添加到班级"，用于记录info日志，如下
     *             $logger->info("准备把用户,{$user['id']}添加到班级");
     */
    abstract protected function getPreparedDebugInfo($user);

    /**
     * @return 如 "把用户,{$user['id']}添加到课程成功,课程ID：{$target['id']},memberId:{$member['id']},订单Id:{$order['id']}"，
     *             用于返回信息及记录info日志
     */
    abstract protected function getFinishedInfo($user, $target, $member, $order, $hasJoined);

    protected function makeUserJoin($userId, $targetId, $data)
    {
        $currentUser = new CurrentUser();
        $systemUser = $this->getUserService()->getUserByType('system');
        $systemUser['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($systemUser);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->biz['user'] = $currentUser;
        $data = $this->prepareOrderFields($data);

        // 如 list($classroom, $member, $order) = $this->getClassroomMemberService()->becomeStudentWithOrder($classroomId, $userId, $data);
        return $this->joinTarget($targetId, $userId, $data);
    }

    /**
     * @return array(
     *                'defaultUrl' => 'https://wyx.marketing.com',
     *                'developerSettingName' => 'marketing_domain',
     *                );
     *                如果 developer setting 内有 相应属性，则使用该属性，否则，使用defaultUrl
     */
    protected function getServerUrlConfig()
    {
        return array(
            'defaultUrl' => 'https://wyx.marketing.com',
            'developerSettingName' => 'marketing_domain',
        );
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    private function createUserIfNeed($postData, $logger)
    {
        $isNew = false;
        $mobile = $postData['mobile'];
        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        $password = '';
        if (empty($user)) {
            $logger->info('根据手机：'.$mobile.',没有查询到用户，准备创建用户');
            $isNew = true;
            $password = substr($mobile, mt_rand(0, 4), 6);
            $postData['password'] = $password;
            $user = $this->createUserFromMarketing($postData);
        }

        return array($isNew, $user, $password);
    }

    private function getUserOrderDeduct($userId, $deduct)
    {
        return array(array(
            'detail' => $deduct['detail'],
            'deduct_type' => $deduct['deduct_type'],
            'deduct_type_name' => $deduct['detail'],
            'deduct_amount' => $deduct['deduct_amount'],  //原价和现价 都需要除以 100，deduct不需要除以 100
            'user_id' => $userId,
        ));
    }

    private function createUserFromMarketing($postData)
    {
        $logger = $this->biz['logger'];
        $token = $this->getTokenService()->makeToken(
            'marketing',
            array(
                'data' => array(
                    'type' => 'marketing',
                ),
                'times' => 1,
                'duration' => 3600,
                'userId' => $postData['user_id'],
            )
        );

        $registration['token'] = $token;
        $registration['verifiedMobile'] = $postData['mobile'];
        $registration['mobile'] = $postData['mobile'];
        $registration['nickname'] = $postData['nickname'];
        $logger->info('Marketing用户名：'.$registration['nickname']);
        $registration['nickname'] = $this->getUserService()->generateNickname($registration);
        $logger->info('ES用户名：'.$registration['nickname']);
        $registration['registeredWay'] = 'web';
        $registration['createdIp'] = isset($postData['client_ip']) ? $postData['client_ip'] : '';
        $registration['password'] = $postData['password'];
        $registration['type'] = 'marketing';

        $user = $this->getAuthService()->register($registration, 'marketing');

        return $user;
    }

    private function tryCreateMarketingOrder($userId, $targetId, $data)
    {
        $product = $this->getProduct($targetId);
        $member = $this->getMember($targetId, $userId);
        $order = $this->getMarketingOrderDao()->getOrderByMarketingOrderId($data['marketingOrderId']);
        if (empty($order)) {
            $data = $this->prepareOrderFields($data);
            $order = $this->createMarketingOrder($targetId, $userId, $data);
        }

        return array($product, $member, $order);
    }

    private function prepareOrderFields($data)
    {
        $data['price'] = $data['marketingOrderPayAmount'];
        $data['originPrice'] = $data['marketingOrderPriceAmount'];
        $data['source'] = 'marketing';
        $data['remark'] = '来自微营销';
        $data['orderTitleRemark'] = '(来自微营销)';

        return $data;
    }

    /**
     * @return MarketingOrderDao
     */
    protected function getMarketingOrderDao()
    {
        return $this->createDao('Marketing:MarketingOrderDao');
    }
}
