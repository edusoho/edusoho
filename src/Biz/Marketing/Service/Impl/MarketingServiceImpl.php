<?php

namespace Biz\Marketing\Service\Impl;

use Biz\BaseService;
use Biz\User\CurrentUser;
use Biz\Role\Util\PermissionBuilder;
use Biz\Marketing\Service\MarketingService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use QiQiuYun\SDK\Auth;
use QiQiuYun\SDK\Service\DrpService;

class MarketingServiceImpl extends BaseService implements MarketingService
{
    /**
     * @param $postData  (价格单位均为分)
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
     *      "order_price_amount":"11000",
     *      "target_id":"19",
     *      "target_type":"course",
     *      "user_id":"10000"
     *  }
     */
    public function addUserToCourse($postData)
    {
        $logger = $this->biz['logger'];
        $logger->debug('验证请求的auth通过，请求认定为合法，处理相应逻辑');
        $logger->debug(json_encode($postData));

        $response = array();
        $response['is_new'] = false;
        $mobile = $postData['mobile'];
        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if (empty($user)) {
            $logger->debug('根据手机：'.$mobile.',没有查询到用户，准备创建用户');
            $response['is_new'] = true;
            $password = substr($mobile, mt_rand(0, 4), 6);
            $postData['password'] = $password;
            $response['password'] = $password;
            $user = $this->createUserFromMarketing($postData);
        }
        $response['user_id'] = $user['id'];

        $logger->debug("准备把用户,{$user['id']}添加到课程");
        $orderInfo = array(
            'marketingOrderId' => $postData['order_id'],
            'marketingOrderPriceAmount' => $postData['order_price_amount'] / 100,  //原价和现价 都需要除以 100，deduct不需要除以 100
            'marketingOrderPayAmount' => $postData['order_pay_amount'] / 100,
            'marketingActivityId' => $postData['activity_id'],
            'marketingActivityName' => $postData['activity_name'],
            'deducts' => $this->getUserOrderDeduct($user['id'], $postData['deduct']),
            'targetType' => 'MarketingCourse',
        );
        $target = array(
            'type' => $postData['target_type'],
            'id' => $postData['target_id'],
        );
        list($course, $member, $order) = $this->makeUserJoinCourse($user['id'], $target['id'], $orderInfo);
        $logger->debug("把用户,{$user['id']}添加到课程成功,课程ID：{$course['id']},memberId:{$member['id']},订单Id:{$order['id']}");
        $response['code'] = 'success';
        $response['msg'] = "把用户,{$user['id']}添加到课程成功,课程ID：{$course['id']},memberId:{$member['id']},订单Id:{$order['id']}";

        return $response;
    }

    public function addUserToClassroom($postData)
    {
        $logger = $this->biz['logger'];
        $logger->debug('验证请求的auth通过，请求认定为合法，处理相应逻辑');
        $logger->debug(json_encode($postData));

        $response = array();
        $response['is_new'] = false;
        $mobile = $postData['mobile'];
        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if (empty($user)) {
            $logger->debug('根据手机：'.$mobile.',没有查询到用户，准备创建用户');
            $response['is_new'] = true;
            $password = substr($mobile, mt_rand(0, 4), 6);
            $postData['password'] = $password;
            $response['password'] = $password;
            $user = $this->createUserFromMarketing($postData);
        }
        $response['user_id'] = $user['id'];

        $logger->debug("准备把用户,{$user['id']}添加到班级");
        $orderInfo = array(
            'marketingOrderId' => $postData['order_id'],
            'marketingOrderPriceAmount' => $postData['order_price_amount'] / 100,  //原价和现价 都需要除以 100，deduct不需要除以 100
            'marketingOrderPayAmount' => $postData['order_pay_amount'] / 100,
            'marketingActivityId' => $postData['activity_id'],
            'marketingActivityName' => $postData['activity_name'],
            'deducts' => $this->getUserOrderDeduct($user['id'], $postData['deduct']),
            'targetType' => 'MarketingCourse',
        );
        $target = array(
            'type' => $postData['target_type'],
            'id' => $postData['target_id'],
        );
        list($classroom, $member, $order) = $this->makeUserJoinClassroom($user['id'], $target['id'], $orderInfo);
        $logger->debug("把用户,{$user['id']}添加到班级成功,班级ID：{$classroom['id']},memberId:{$member['id']},订单Id:{$order['id']}");
        $response['code'] = 'success';
        $response['msg'] = "把用户,{$user['id']}添加到班级成功,班级ID：{$classroom['id']},memberId:{$member['id']},订单Id:{$order['id']}";

        return $response;
    }

    public function getDrpService()
    {
        if (empty($this->drpService)) {
            $developerSetting = $this->getSettingService()->get('developer', array());
            $config = $this->getServerUrlConfig();
            $serverUrl = $config['defaultUrl'];
            if (!empty($developerSetting[$config['developerSettingName']])) {
                $serverUrl = $developerSetting[$config['developerSettingName']];
            }

            $this->drpService = null;
            $settings = $this->getSettingService()->get('storage', array());
            if (!empty($settings['cloud_access_key']) && !empty($settings['cloud_secret_key'])) {
                $auth = new Auth($settings['cloud_access_key'], $settings['cloud_secret_key']);
                $this->drpService = new DrpService($auth, array('base_uri' => $serverUrl));
            }
        }

        return $this->drpService;
    }

    /**
     * @return array(
     *                'defaultUrl' => 'http://wyx.marketing.com',
     *                'developerSettingName' => 'marketing_domain',
     *                );
     *                如果 developer setting 内有 相应属性，则使用该属性，否则，使用defaultUrl
     */
    protected function getServerUrlConfig()
    {
        return array(
            'defaultUrl' => 'http://wyx.marketing.com',
            'developerSettingName' => 'marketing_domain',
        );
    }

    private function getUserOrderDeduct($userId, $deduct)
    {
        return array(array(
            'detail' => $deduct['detail'],
            'deduct_type' => $deduct['deduct_type'],
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
        $logger->debug('Marketing用户名：'.$registration['nickname']);
        $registration['nickname'] = $this->getUserService()->generateNickname($registration);
        $logger->debug('ES用户名：'.$registration['nickname']);
        $registration['registeredWay'] = 'web';
        $registration['createdIp'] = isset($postData['client_ip']) ? $postData['client_ip'] : '';
        $registration['password'] = $postData['password'];
        $registration['type'] = 'marketing';

        $user = $this->getAuthService()->register($registration, 'marketing');

        return $user;
    }

    private function makeUserJoinCourse($userId, $courseId, $data)
    {
        $currentUser = new CurrentUser();
        $systemUser = $this->getUserService()->getUserByType('system');
        $systemUser['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($systemUser);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->biz['user'] = $currentUser;
        $data['price'] = $data['marketingOrderPayAmount'];
        $data['originPrice'] = $data['marketingOrderPriceAmount'];
        $data['source'] = 'marketing';
        $data['remark'] = '来自微营销';
        $data['orderTitleRemark'] = '(来自微营销)';

        list($course, $member, $order) = $this->getCourseMemberService()->becomeStudentAndCreateOrder($userId, $courseId, $data);

        return array($course, $member, $order);
    }

    /**
     * @param $userId
     * @param $classroomId
     * @param $data
     *
     * @return array
     *
     * @throws \Codeages\Biz\Framework\Service\Exception\NotFoundException
     * @throws \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @throws \Exception
     */
    private function makeUserJoinClassroom($userId, $classroomId, $data)
    {
        $currentUser = new CurrentUser();
        $systemUser = $this->getUserService()->getUserByType('system');
        $systemUser['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($systemUser);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $this->biz['user'] = $currentUser;
        $data['price'] = $data['marketingOrderPayAmount'];
        $data['originPrice'] = $data['marketingOrderPriceAmount'];
        $data['source'] = 'marketing';
        $data['remark'] = '来自微营销';
        $data['orderTitleRemark'] = '(来自微营销)';

        list($classroom, $member, $order) = $this->getClassroomMemberService()->becomeStudentWithOrder($classroomId, $userId, $data);

        return array($classroom, $member, $order);
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

    /**
     * @return MarketingCourseMemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Marketing:MarketingCourseMemberService');
    }

    /**
     * @return MarketingClassroomMemberServiceImpl
     */
    protected function getClassroomMemberService()
    {
        return $this->createService('Marketing:MarketingClassroomMemberService');
    }
}
