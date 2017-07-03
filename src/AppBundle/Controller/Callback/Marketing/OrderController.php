<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use Biz\User\CurrentUser;
use Biz\Role\Util\PermissionBuilder;
use Codeages\Weblib\Auth\Authentication;

class OrderController extends MarketingBaseController
{
    public function indexAction(Request $request)
    {
        $logger = $this->getBiz()['logger'];
        $logger->debug('营销平台通知处理订单');
        $content = $request->getContent();
        $logger->debug('Postcontent:'.$content);
        $postData = json_decode($content, true);
        $keyProvider = new AuthKeyProvider();
        $authentication = new Authentication($keyProvider);
        try {
            $logger->debug('准备验证auth');
            $authentication->auth($request);
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
            $logger->debug("准备把用户,{$user['id']}添加到课程");
            $orderInfo = array(
                'marketingOrderId' => $postData['order_id'],
                'marketingOrderPayAmount' => $postData['order_pay_amount'],
                'marketingActivityId' => $postData['activity_id'],
                'marketingActivityName' => $postData['activity_name'],
            );
            $target = array(
                'type' => $postData['target_type'],
                'id' => $postData['target_id'],
            );
            list($course, $member, $order) = $this->userJoin($user['id'], $target['id'], $orderInfo);
            $logger->debug("把用户,{$user['id']}添加到课程成功,课程ID：{$course['id']},memberId:{$member['id']},订单Id:{$order['id']}");
            $response['user_id'] = $user['id'];
            $response['is_new'] = $isNew;
            $response['code'] = 'success';

            return $response;
        } catch (\Exception $e) {
            return array('code' => 'error', 'msg' => $e->getMessage());
        }
    }

    private function createUserFromMarketing($postData, $request)
    {
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
        $registration['nickname'] = $this->getUserService()->generateNickname($postData['nickname']);
        $registration['registeredWay'] = 'web';
        $registration['createdIp'] = $request->getClientIp();
        $registration['password'] = $postData['password'];
        $registration['type'] = 'marketing';

        $user = $this->getAuthService()->register($registration, 'marketing');

        return $user;
    }

    private function userJoin($userId, $courseId, $data)
    {
        $currentUser = new CurrentUser();
        $systemUser = $this->getUserService()->getUserByType('system');
        $systemUser['currentIp'] = '127.0.0.1';
        $currentUser->fromArray($systemUser);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $biz = $this->getBiz();
        $biz['user'] = $currentUser;

        $data['price'] = $data['marketingOrderPayAmount'];
        $data['payment'] = 'marketing';
        $data['remark'] = '来自营销平台';
        $data['orderTitleRemark'] = '(来自营销平台)';
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
