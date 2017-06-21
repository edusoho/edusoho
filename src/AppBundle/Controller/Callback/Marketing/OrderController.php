<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use Biz\User\CurrentUser;
use Biz\Role\Util\PermissionBuilder;
use Codeages\Weblib\Auth\Authentication;
use AppBundle\Component\WeblibAuth\AuthKeyProvider;

class OrderController extends BaseController
{
    public function processAction(Request $request)
    {
        $keyProvider = new AuthKeyProvider();
        $authentication = new Authentication($keyProvider);
        try {
            $authentication->auth($request);
            $response = array();
            $isNew = false;
            $postData = $request->request->all();
            $mobile = $postData['mobile'];
            $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
            if (empty($user)) {
                $isNew = true;
                $password = substr($mobile, mt_rand(0, 4), 6);
                $postData['password'] = $password;
                $response['password'] = $password;
                $user = $this->createUserFromMarketing($postData, $request);
            }
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
            $this->userJoin($user['id'], $target['id'], $orderInfo);

            $response['user_id'] = $user['id'];
            $response['is_new'] = $isNew;
            $response['code'] = 'success';

            return $this->createJsonResponse($response);
        } catch (\Exception $e) {
            return $this->createJsonResponse(['code' => 'error', 'msg' => $e->getMessage()]);
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
        $registration['password'] = 12345;
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
        $this->getMemberService()->becomeStudentAndCreateOrder($userId, $courseId, $data);
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
