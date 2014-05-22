<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\User\CurrentUser;
use Topxia\Common\ArrayToolkit;

class MobileController extends BaseController
{

    /**
    *GetHostByName($_SERVER['SERVER_NAME']);
    */
    public static $webHost = "http://try3.edusoho.cn/files";

    public static $baseUrl = "http://try3.edusoho.cn";

    public static $mobileType = "mobileLogin";

    public static $defLimit = 8;

    protected $result = array();

    protected function setResultStatus($status = "error")
    {
        $this->result['status'] = $status;
    }

    protected function createJson(Request $request, $data)
    {
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
    
    protected function setPage($result, $page, $count)
    {
        $result['page'] = $page;
        $total_page = $count % self::$defLimit == 0 ? $count / self::$defLimit : intval($count / self::$defLimit) + 1;
        $result['total_page'] = $total_page;
        return $result;
    }

    protected function changeCreatedTime($course_comment)
    {
        for ($i=0; $i<count($course_comment); $i++) {
            $temp = $course_comment[$i];
            $course_comment[$i]['createdTime'] = date('Y-m-d H:i:s', $temp['createdTime']);
        }
        return $course_comment;
    }

    protected function changeUserPicture($users, $isArray)
    {
        if ($isArray) {
            $keys = array_keys($users);
            foreach($keys as $i) {
                $users[$i] = $this->_changeUserPicture($users[$i]);
            }
        } else {
            $users = $this->_changeUserPicture($users);
        }
        
        return $users;
    }

    protected function _changeUserPicture($user)
    {
        $user['smallAvatar'] = $user['smallAvatar'] ? self::$webHost. '/' . str_replace('public://', '', $user['smallAvatar']) : null;
        $user['mediumAvatar'] = $user['mediumAvatar'] ? self::$webHost. '/' . str_replace('public://', '', $user['mediumAvatar']) : null;
        $user['largeAvatar'] = $user['largeAvatar'] ? self::$webHost. '/' . str_replace('public://', '', $user['largeAvatar']) : null;
        return $user;
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
        $token = $this->getUserService()->getToken(self::$mobileType, $token);
        if ($token) {
            $this->setCurrentUser($token['userId'], $request);
        }
        return $token;
    }

    protected function createToken($user, $request)
    {
        $token = $this->getUserService()->makeToken(self::$mobileType, $user['id'], time() + 3600 * 24 * 30);
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

    protected function createErrorResponse($code, $message)
    {
        $error = array('error' => $code, 'message' => $message);
        return new JsonResponse($error);
    }

}
