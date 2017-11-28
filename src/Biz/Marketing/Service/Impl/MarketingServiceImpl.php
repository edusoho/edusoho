<?php

namespace Biz\Marketing\Service\Impl;

use Biz\BaseService;
use Biz\User\CurrentUser;
use Biz\Role\Util\PermissionBuilder;
use Biz\Marketing\Service\MarketingService;

class MarketingServiceImpl extends BaseService implements MarketingService
{
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
            'marketingOrderPriceAmount' => $postData['order_price_amount'],
            'marketingOrderPayAmount' => $postData['order_pay_amount'],
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

    private function getUserOrderDeduct($userId, $deduct)
    {
        return array(array(
            'detail' => $deduct['detail'],
            'deduct_type' => $deduct['deduct_type'],
            'deduct_amount' => $deduct['deduct_amount'],
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

        list($course, $member, $order) = $this->getMemberService()->becomeStudentAndCreateOrder($userId, $courseId, $data);

        return array($course, $member, $order);
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getMemberService()
    {
        return $this->createService('Marketing:MarketingMemberService');
    }
}
