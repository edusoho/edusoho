<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\User\CurrentUser;
use Topxia\Common\ArrayToolkit;

class MobileController extends BaseController
{
    const TOKEN_TYPE = 'mobile_login';
    const MOBILE_MODULE = "mobile";

    protected $result = array();

    public function mobileVersionAction(Request $request)
    {
        $result = array(
            'mobileVersion' => 1,
            'url' => $request->getSchemeAndHttpHost()
            );

        return $this->createJson($request, $result);
    }

    public function mobileSchoolLoginAction(Request $request)
    {
        if ($request->getMethod() == "POST"){
            $parames = $request->request->all();
        } else {
            $parames = $request->query->all();
        }

        $this->getLogService()->info(MobileController::MOBILE_MODULE, "school_login", "网校登录",  $parames);

        return $this->createJson($request, null);
    }

    public function mobileDeviceRegistAction(Request $request)
    {
        $result = false;
        $parames = array();
        $parames["imei"] = $this->getPostParam($request, "imei",  "");
        $parames["platform"] = $this->getPostParam($request, "platform",  "");
        $parames["version"] = $this->getPostParam($request, "version",  "");
        $parames["screenresolution"] = $this->getPostParam($request, "screenresolution",  "");
        $parames["kernel"] = $this->getPostParam($request, "kernel",  "");

        if (empty($parames["imei"]) || empty($parames["platform"])) {
            return $this->createErrorResponse($request, "info_error", "串号或平台版本不能为空!");
        }
        if ($this->getMobileDeviceService()->addMobileDevice($parames)) {
            $result = true;
        }
        
        $this->getLogService()->info(MobileController::MOBILE_MODULE, "regist_device", "注册客户端",  $parames);
        return $this->createJson($request, $result);
    }

    protected function getPostParam($request, $name, $default = null)
    {
        $result = $request->request->get($name);
        return $result ? $result : $default;
    }
    
    public function notifyMobileVersionAction(Request $request)
    {
        return new JsonResponse("success");
    }

    protected function createJson(Request $request, $data)
    {
        if (empty($data['error'])) {
            $mobile = $this->setting('mobile', array());
            if (empty($mobile['enabled'])) {
                return $this->createErrorResponse($request, 'client_closed', '网校客户端功能已关闭');
            }
        }

        $callback = $request->query->get('callback');
        if ($callback) {
            return $this->createJsonP($request, $callback, $data);
        } else {
            return new JsonResponse($data);
        }
    }

    protected function createJsonP(Request $request, $callback, $data)
    {
        $response = new JsonResponse($data);
        $response->setCallback($callback);
        return $response;
    }

    protected function getParam($request, $name, $default = null)
    {
        $result = $request->query->get($name);
        return $result ? $result : $default;
    }

    private function setCurrentUser($userId, $request)
    {
        $user = $this->getUserService()->getUser($userId);
        $currentUser = new CurrentUser();
        if ($user) {
            $user['currentIp'] = $request->getClientIp();
        } else {
            $user = array('id' => 0);
        }
        $currentUser = $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
    }

    protected function getUserToken($request)
    {
        $token = $request->query->get('token');
        $token = $this->getUserService()->getToken(self::TOKEN_TYPE, $token);
        if ($token) {
            $this->setCurrentUser($token['userId'], $request);
        }
        return $token;
    }

    protected function createToken($user, $request)
    {
        $token = $this->getUserService()->makeToken(self::TOKEN_TYPE, $user['id'], time() + 3600 * 24 * 30);
        if ($token) {
            $this->setCurrentUser($user['id'], $request);
        }
        return $token;
    }

    public function simpleUser($user) 
    {
        if (empty($user)) {
            return null;
        }
        $users = $this->simplifyUsers(array($user));
        return current($users);
    }

    public function simplifyUsers($users)
    {
        if (empty($users)) {
            return array();
        }

        $simplifyUsers = array();
        foreach ($users as $key => $user) {
            $simplifyUsers[$key] = array (
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'title' => $user['title'],
                'avatar' => $this->container->get('topxia.twig.web_extension')->getFilePath($user['smallAvatar'], 'avatar.png', true),
            );
        }

        return $simplifyUsers;
    }

    /**
     * @todo 要移走，放这里不合适
     */
    protected function filterReviews($reviews)
    {
        if (empty($reviews)) {
            return array();
        }

        $userIds = ArrayToolkit::column($reviews, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $self = $this;
        return array_map(function($review) use ($self, $users) {
            $review['user'] = empty($users[$review['userId']]) ? null : $self->simpleUser($users[$review['userId']]);
            unset($review['userId']);

            $review['createdTime'] = date('c', $review['createdTime']);
            return $review;
        }, $reviews);
    }

    /**
     * @todo 要移走，放这里不合适
     */
    protected function filterReview($review)
    {
        if (empty($review)) {
            return null;
        }

        $reviews = $this->filterReviews(array($review));
        return current($reviews);
    }

    protected function createErrorResponse($request, $name, $message)
    {
        $error = array('error' => array('name' => $name, 'message' => $message));
        return $this->createJson($request, $error);
    }

    protected function getMobileDeviceService()
    {
        return $this->getServiceKernel()->createService('Util.MobileDeviceService');
    }

}
