<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use Biz\User\CurrentUser;
use Biz\Role\Util\PermissionBuilder;
use Codeages\Weblib\Auth\Authentication;

class Orders extends MarketingBase
{
    public function accept(Request $request)
    {
        $biz = $this->getBiz();
        $logger = $biz['logger'];
        $logger->debug('微营销通知处理订单');
        $content = $request->getContent();
        $postData = json_decode($content, true);

        $keyProvider = new AuthKeyProvider();
        $authentication = new Authentication($keyProvider);
        try {
            $logger->debug('准备验证auth');
            $authentication->auth($request);
            $logger->debug('验证请求的auth通过，请求认定为合法，处理相应逻辑');
            $logger->debug(json_encode($postData));

            $response = array();
            $isNew = false;
            $mobile = $postData['mobile'];
            $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
            if (empty($user)) {
                $logger->debug('根据手机：'.$mobile.',没有查询到用户，准备创建用户');
                $isNew = true;
                $password = substr($mobile, mt_rand(0, 4), 6);
                $postData['password'] = $password;
                $response['password'] = $password;
                $user = $this->createUserFromMarketing($postData, $request);
            }
            $response['user_id'] = $user['id'];
            $response['is_new'] = $isNew;

            $logger->debug("准备把用户,{$user['id']}添加到课程");
            $orderInfo = array(
                'marketingOrderId' => $postData['order_id'],
                'marketingOrderPriceAmount' => $postData['order_price_amount'],
                'marketingOrderPayAmount' => $postData['order_pay_amount'],
                'marketingActivityId' => $postData['activity_id'],
                'marketingActivityName' => $postData['activity_name'],
                'deducts' => $this->getUserOrderDeduct($user['id'], $postData['deduct']),
            );
            $target = array(
                'type' => $postData['target_type'],
                'id' => $postData['target_id'],
            );
            list($course, $member, $order) = $this->makeUserJoinCourse($user['id'], $target['id'], $orderInfo);
            $logger->debug("把用户,{$user['id']}添加到课程成功,课程ID：{$course['id']},memberId:{$member['id']},订单Id:{$order['id']}");
            $response['code'] = 'success';
            $response['msg'] = "把用户,{$user['id']}添加到课程成功,课程ID：{$course['id']},memberId:{$member['id']},订单Id:{$order['id']}";
        } catch (\Exception $e) {
            $response['code'] = 'error';
            $response['msg'] = 'ES处理微营销订单失败,'.$e->getMessage();
            $logger->error($e);
        }

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

    private function createUserFromMarketing($postData, $request)
    {
        $biz = $this->getBiz();
        $logger = $biz['logger'];
        $token = $this->getTokenService()->makeToken('marketing', array(
            'data' => array(
                'type' => 'marketing',
            ),
            'times' => 1,
            'duration' => 3600,
            'userId' => $postData['user_id'],
            ));

        $registration['token'] = $token;
        $registration['verifiedMobile'] = $postData['mobile'];
        $registration['mobile'] = $postData['mobile'];
        $registration['nickname'] = $postData['nickname'];
        $logger->debug('Marketing用户名：'.$registration['nickname']);
        $registration['nickname'] = $this->getUserService()->generateNickname($registration);
        $logger->debug('ES用户名：'.$registration['nickname']);
        $registration['registeredWay'] = 'web';
        $registration['createdIp'] = $request->getClientIp();
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
        $biz = $this->getBiz();
        $biz['user'] = $currentUser;
        $data['price'] = $data['marketingOrderPriceAmount'] * 100;
        $data['source'] = 'marketing';
        $data['remark'] = '来自微营销';
        $data['orderTitleRemark'] = '(来自微营销)';

        list($course, $member, $order) = $this->getMemberService()->becomeStudentAndCreateOrder($userId, $courseId, $data);

        return array($course, $member, $order);
    }

    protected function getAuthService()
    {
        return $this->getBiz()->service('User:AuthService');
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
        return $this->createService('Course:MemberService');
    }
}
