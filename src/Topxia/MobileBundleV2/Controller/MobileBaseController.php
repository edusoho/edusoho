<?php

namespace Topxia\MobileBundleV2\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Service\User\CurrentUser;
use Topxia\Common\ArrayToolkit;

class MobileBaseController extends BaseController
{
    const MOBILE_MODULE = 'mobile';
    const TOKEN_TYPE = 'mobile_login';

    protected $result = array();

    public function mobileVersionAction(Request $request)
    {
        $result = array(
            'mobileVersion' => 1,
            'url' => $request->getSchemeAndHttpHost()
            );

        return $this->createJson($request, $result);
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
        $result = $request->request->get($name);
        return $result ? $result : $default;
    }

    public function getUser()
    {
        return $this->getCurrentUser();
    }

    public function setting($name, $default = null)
    {
        return $this->get('topxia.twig.web_extension')->getSetting($name, $default);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getService($name)
    {
        return $this->getServiceKernel()->createService($name);
    }

    public function setCurrentUser($userId, $request)
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

    public function getUserToken($request)
    {
        $token = $this->getToken($request);
        $token = $this->getUserService()->getToken(self::TOKEN_TYPE, $token);
        if ($token) {
            $this->setCurrentUser($token['userId'], $request);
        }
        return $token;
    }

    public function getToken($request)
    {
        if ($request->getMethod() == "POST") {
            $token = $request->headers->get('token', '');
        } else {
            $token = $request->query->get('token', '');
        }

        return $token;
    }

    public function getUserByToken($request)
    {
        $token = $this->getToken($request);
        $token = $this->getUserService()->getToken(self::TOKEN_TYPE, $token);
        if ($token) {
            $this->setCurrentUser($token['userId'], $request);
        }

        return $this->getUser();
    }

    public function autoLogin($user)
    {
        $user = $this->getUserService()->getUser($user->id);
        $this->authenticateUser($user);
    }

    public function createToken($user, $request)
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

        $controller = $this;

        $simplifyUsers = array();
        foreach ($users as $key => $user) {
            $simplifyUsers[$key] = array (
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'title' => $user['title'],
                'following' => $controller->getUserService()->findUserFollowingCount($user['id']),
                'follower' => $controller->getUserService()->findUserFollowerCount($user['id']),
                'avatar' => $this->container->get('topxia.twig.web_extension')->getFilePath($user['smallAvatar'], 'avatar.png', true),
            );
        }

        return $simplifyUsers;
    }

    /**
     * @todo 要移走，放这里不合适
     */
    public function filterReviews($reviews)
    {
        if (empty($reviews)) {
            return array();
        }

        $userIds = ArrayToolkit::column($reviews, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $self = $this;
        return array_map(function($review) use ($self, $users) {
            $review['user'] = empty($users[$review['userId']]) ? null : $self->filterUser($users[$review['userId']]);
            unset($review['userId']);

            $review['createdTime'] = date('c', $review['createdTime']);
            return $review;
        }, $reviews);
    }

    public function filterCourse($course)
    {
        if (empty($course)) {
            return null;
        }

        $courses = $this->filterCourses(array($course));

        return current($courses);
    }
    
    public function filterCourses($courses)
    {
        if (empty($courses)) {
            return array();
        }

        $teacherIds = array();
        foreach ($courses as $course) {
            $teacherIds = array_merge($teacherIds, $course['teacherIds']);
        }
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $teachers = $this->simplifyUsers($teachers);

        $self = $this;
        $container = $this->container;
        return array_map(function($course) use ($self, $container, $teachers) {
            $course['smallPicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['smallPicture'], 'course-large.png', true);
            $course['middlePicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['middlePicture'], 'course-large.png', true);
            $course['largePicture'] = $container->get('topxia.twig.web_extension')->getFilePath($course['largePicture'], 'course-large.png', true);
            $course['about'] = $self->convertAbsoluteUrl($container->get('request'), $course['about']);

            $course['teachers'] = array();
            foreach ($course['teacherIds'] as $teacherId) {
                $course['teachers'][] = $teachers[$teacherId];
            }
            unset($course['teacherIds']);

            return $course;
        }, $courses);
    }

    public function convertAbsoluteUrl($request, $html)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function($matches) use ($baseUrl) {
            return "src=\"{$baseUrl}/{$matches[1]}\"";
        }, $html);

        return $html;

    }

    public function filterUser($user)
    {
        if (empty($user)) {
            return null;
        }
        
        $users = $this->filterUsers(array(
            $user
        ));
        
        return current($users);
    }
    
    public function filterItems($items)
    {
        if (empty($items)) {
            return array();
        }

        $self = $this;
        $container = $this->container;

        return array_map(function($item) use ($self, $container) {
            $item['createdTime'] = date('c', $item['createdTime']);
            if (!empty($item['length']) and in_array($item['type'], array('audio', 'video'))) {
                $item['length'] =  $container->get('topxia.twig.web_extension')->durationFilter($item['length']);
            } else {
                $item['length'] = "";
            }

            if (empty($item['content'])) {
                $item['content'] = "";
            }
            $item['content'] = $self->convertAbsoluteUrl($container->get('request'), $item['content']);

            return $item;
        }, $items);

    }

    public function coverPath($path, $coverPath)
    {
        return $this->container->get('topxia.twig.web_extension')->getFilePath($path, $coverPath, true);
    }

    public function filterUsers($users)
    {
        if (empty($users)) {
            return array();
        }
        
        $container = $this->container;

        $controller = $this;
        return array_map(function($user) use ($container, $controller)
        {
            $user['smallAvatar']  = $container->get('topxia.twig.web_extension')->getFilePath($user['smallAvatar'], 'avatar.png', true);
            $user['mediumAvatar'] = $container->get('topxia.twig.web_extension')->getFilePath($user['mediumAvatar'], 'avatar.png', true);
            $user['largeAvatar']  = $container->get('topxia.twig.web_extension')->getFilePath($user['largeAvatar'], 'avatar-large.png', true);
            $user['createdTime']  = date('c', $user['createdTime']);
            
            $user['email'] = '';
            
            if ($controller->setting('vip.enabled')) {
                $vip = $controller->getVipService()->getMemberByUserId($user['id']);
                $user["vip"] = $vip;
            }
            $userProfile = $controller->getUserService()->getUserProfile($user['id']);
            $user['signature'] = $userProfile['signature'];
            if(isset($user['about']))
            {
                $user['about'] = $controller->convertAbsoluteUrl($controller->request, $userProfile['about']);
            }

            $user['following'] = $controller->getUserService()->findUserFollowingCount($user['id']);
            $user['follower'] = $controller->getUserService()->findUserFollowerCount($user['id']);

            unset($user['password']);
            unset($user['salt']);
            unset($user['createdIp']);
            unset($user['loginTime']);
            unset($user['loginIp']);
            unset($user['loginSessionId']);
            unset($user['newMessageNum']);
            unset($user['newNotificationNum']);
            unset($user['promoted']);
            unset($user['promotedTime']);
            unset($user['approvalTime']);
            unset($user['approvalStatus']);
            unset($user['tags']);
            unset($user['point']);
            unset($user['coin']);
            
            return $user;
        }, $users);
    }

    public function filterLiveCourses($user, $start, $limit){
        $courses = $this->getCourseService()->findUserLeaningCourses($user['id'], $start, $limit);
        
        $tempCourses = array();
        $tempCourseIds = array();
        foreach ($courses as $key => $course) {  
            if(!strcmp($course["type"],"live"))
            {
                $tempCourses[$course["id"]] = $course;
                $tempCourseIds[] = $course["id"];
            }
        }

        $tempLiveLessons = array();
        $tempCourseIdIndex = 0;
        $tempLessons = array();
        for($tempCourseIdIndex; $tempCourseIdIndex < sizeof($tempCourseIds); $tempCourseIdIndex++)
        {
            $tempLiveLessons = $this->getCourseService()->getCourseLessons($tempCourseIds[$tempCourseIdIndex]);
            if(isset($tempLiveLessons)){
                $tempLessons[$tempCourseIds[$tempCourseIdIndex]] = $tempLiveLessons;
                // unset($tempLiveLessons);
            }
        }

        $nowTime = time();
        $liveLessons = array();
        $tempLiveLesson;
        $recentlyLiveLessonStartTime;
        $tempLessonIndex;
        // $emptyLessonCourseId = array();
        // $tempCoursesIndex = 0;

        foreach($tempLessons as $key => $tempLesson){
            if(!sizeof($tempLesson)){
                // $emptyLessonCourseId[$key] = $tempCoursesIndex;
                // $tempCoursesIndex++;
                continue;
            }
            if($nowTime <= $tempLesson[0]["endTime"]){
                $tempLiveLesson = $tempLesson[0];
            }
            if(sizeof($tempLesson) > 1){
                $recentlyLiveLessonStartTime = 2*$nowTime;
                for($tempLessonIndex=0; $tempLessonIndex < sizeof($tempLesson); $tempLessonIndex++){
                    if($tempLesson[$tempLessonIndex]["endTime"] >= $nowTime){
                        if($tempLesson[$tempLessonIndex]["startTime"] < $recentlyLiveLessonStartTime){
                            $recentlyLiveLessonStartTime = $tempLesson[$tempLessonIndex]["startTime"];
                            $tempLiveLesson = $tempLesson[$tempLessonIndex];
                        }
                    }
                }
            }
            if(isset($tempLiveLesson)){
                $liveLessons[$key] = $tempLiveLesson;
                unset($tempLiveLesson);
            }
            // $tempCoursesIndex++;
        }

        foreach($tempCourses as $key => $value){
            if(isset($liveLessons[$key])){
                $tempCourses[$key]["liveLessonTitle"] = $liveLessons[$key]["title"];
                $tempCourses[$key]["liveStartTime"] = date("c", $liveLessons[$key]["startTime"]);
                $tempCourses[$key]["liveEndTime"] = date("c", $liveLessons[$key]["endTime"]);
            }else{
                $tempCourses[$key]["liveLessonTitle"] = "";
                $tempCourses[$key]["liveStartTime"] = "";
                $tempCourses[$key]["liveEndTime"] = "";
            }
        }

        // foreach($tempCourses as $key => $value){
        //     if(isset($emptyLessonCourseId[$key])){
        //         array_splice($tempCourses, $emptyLessonCourseId[$key], 1);
        //     }
        // }

        return $tempCourses;
    }

    public function filterOneLiveCourseByDESC($user){
        $learningCourseTotal = $this->getCourseService()->findUserLeaningCourseCount($user['id']);

        $resultLiveCourses = $this->filterLiveCourses($user, 0, $learningCourseTotal);

        return $resultLiveCourses;
    }

    /**
     * @todo 要移走，放这里不合适
     */
    public function filterReview($review)
    {
        if (empty($review)) {
            return null;
        }

        $reviews = $this->filterReviews(array($review));
        return current($reviews);
    }

    public function createErrorResponse($request, $name, $message)
    {
        $error = array('error' => array('name' => $name, 'message' => $message));
        return $this->createJson($request, $error);
    }

    public function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
    
    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    public function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

    public function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    public function getMemberDao ()
    {
        return $this->getServiceKernel()->createDao('Course.CourseMemberDao');
    }

    public function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    public function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    public function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    public function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    public function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    public function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    public function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }

    public function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    public function getNoteService(){
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

}
