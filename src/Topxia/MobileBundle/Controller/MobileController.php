<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\User\CurrentUser;

class MobileController extends BaseController
{

    /**
    *GetHostByName($_SERVER['SERVER_NAME']);
    */
    public static $webHost = "http://192.168.12.7/files";

    public static $baseUrl = "http://192.168.12.7";

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

    protected function changeCoursePicture($course, $isArray)
    {
        if ($isArray) {
            for ($i=0; $i<count($course); $i++) {
                $course[$i] = $this->_changeCoursePicture($course[$i]);
            }
        } else {
            $course = $this->_changeCoursePicture($course);
        }
        
        return $course;
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

    protected function _changeCoursePicture($course)
    {
        $course['smallPicture'] = $course['smallPicture'] ? self::$webHost. '/' . str_replace('public://', '', $course['smallPicture']) : null;
        $course['middlePicture'] = $course['middlePicture'] ? self::$webHost. '/' . str_replace('public://', '', $course['middlePicture']) : null;
        $course['largePicture'] = $course['largePicture'] ? self::$webHost. '/' . str_replace('public://', '', $course['largePicture']) : null;
        return $course;
    }

    private function setCurrentUser($userId, $request)
    {
        $user = $this->getUserService()->getUser($userId);
        if ($user) {
            $currentUser = new CurrentUser();
            $user['currentIp'] = $request->getClientIp();
            $currentUser = $currentUser->fromArray($user);
            $this->getServiceKernel()->setCurrentUser($currentUser);
        }
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
}
