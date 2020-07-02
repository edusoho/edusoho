<?php

namespace Topxia\MobileBundleV2\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Util\CourseTitleUtils;
use Biz\Review\Service\ReviewService;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Api\Util\TagUtil;

class MobileBaseController extends BaseController
{
    const MOBILE_MODULE = 'mobile';
    const TOKEN_TYPE = 'mobile_login';

    protected $result = [];

    public function mobileVersionAction(Request $request)
    {
        $result = [
            'mobileVersion' => 1,
            'url' => $request->getSchemeAndHttpHost(),
        ];

        return $this->createJson($request, $result);
    }

    public function createJson(Request $request, $data)
    {
        $callback = $request->query->get('callback');

        if ($callback) {
            return $this->createJsonP($request, $callback, $data);
        } else {
            $response = new JsonResponse($data);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, GET');
            $response->headers->set('Access-Control-Request-Headers', 'token');
            $response->headers->set('Access-Control-Max-Age', '30');

            return $response;
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

    public function setting($name, $default = null)
    {
        return $this->get('web.twig.extension')->getSetting($name, $default);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getService($name)
    {
        return $this->createService($name);
    }

    public function isinstalledPlugin($name)
    {
        return $this->isPluginInstalled($name);
    }

    public function setCurrentUser($userId, $request)
    {
        $user = $this->getUserService()->getUser($userId);
        $currentUser = new CurrentUser();

        if (empty($user)) {
            $user = ['id' => 0];
        }
        $user['currentIp'] = $request->getClientIp();

        $currentUser = $currentUser->fromArray($user);

        $permissions = PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles());
        $currentUser->setPermissions($permissions);

        $biz = $this->getBiz();
        $biz['user'] = $currentUser;
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
        $token = $request->headers->get('token', '');

        if (empty($token) && 'GET' == $request->getMethod()) {
            $token = $request->query->get('token', '');
        }

        if (empty($token)) {
            $token = $request->cookies->get('token');
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

        $users = $this->simplifyUsers([$user]);

        return current($users);
    }

    public function simplifyUsers($users)
    {
        if (empty($users)) {
            return [];
        }

        $controller = $this;

        $simplifyUsers = [];

        foreach ($users as $key => $user) {
            $simplifyUsers[$key] = [
                'id' => $user['id'],
                'nickname' => (1 == $user['destroyed']) ? '帐号已注销' : $user['nickname'],
                'title' => $user['title'],
                'following' => (string) $controller->getUserService()->findUserFollowingCount($user['id']),
                'follower' => (string) $controller->getUserService()->findUserFollowerCount($user['id']),
                'avatar' => $this->container->get('web.twig.extension')->getFurl(
                    $user['smallAvatar'],
                    'avatar.png',
                    'default'
                ),
            ];
        }

        return $simplifyUsers;
    }

    /**
     * @todo 要移走，放这里不合适
     */
    public function filterReviews($reviews)
    {
        if (empty($reviews)) {
            return [];
        }

        $userIds = ArrayToolkit::column($reviews, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $self = $this;

        return array_map(
            function ($review) use ($self, $users) {
                $review['user'] = empty($users[$review['userId']]) ? null : $self->filterUser(
                    $users[$review['userId']]
                );
                unset($review['userId']);

                $review['createdTime'] = date('c', $review['createdTime']);
                $review['updatedTime'] = date('c', $review['updatedTime']);

                return $review;
            },
            $reviews
        );
    }

    public function filterCourse($course)
    {
        if (empty($course)) {
            return null;
        }

        $courses = $this->filterCourses([$course]);

        return end($courses);
    }

    public function getCoinSetting()
    {
        $coinSetting = $this->setting('coin');

        if (empty($coinSetting)) {
            return null;
        }

        $coinEnabled = isset($coinSetting['coin_enabled']) && $coinSetting['coin_enabled'];

        if (empty($coinEnabled)) {
            return null;
        }

        $cashRate = 1;

        if (isset($coinSetting['cash_rate'])) {
            $cashRate = $coinSetting['cash_rate'];
        }

        $coin = [
            'cashRate' => $cashRate,
            'priceType' => isset($coinSetting['price_type']) ? $coinSetting['price_type'] : 'RMB',
            'name' => isset($coinSetting['coin_name']) ? $coinSetting['coin_name'] : '虚拟币',
        ];

        return $coin;
    }

    public function filterCourses($courses)
    {
        if (empty($courses)) {
            return [];
        }

        $teacherIds = [];

        foreach ($courses as $key => $course) {
            if (isset($course['teacherIds']) && !empty($course['teacherIds'])) {
                if (!is_array($course['teacherIds'])) {
                    $courses[$key]['teacherIds'] = $course['teacherIds'] = explode('|', $course['teacherIds']);
                }
                $teacherIds = array_merge($teacherIds, $course['teacherIds']);
            }
        }

        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $teachers = $this->simplifyUsers($teachers);

        $coinSetting = $this->getCoinSetting();

        $self = $this;
        $container = $this->container;

        $courseIds = ArrayToolkit::column($courses, 'id');
        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);
        foreach ($courses as &$course) {
            $courseSet = $courseSets[$course['courseSetId']];

            $course = $this->convertOldFields($course);
            $course = $this->filledCourseByCourseSet($course, $courseSet);

            $small = empty($courseSet['cover']['small']) ? '' : $courseSet['cover']['small'];
            $middle = empty($courseSet['cover']['middle']) ? '' : $courseSet['cover']['middle'];
            $large = empty($courseSet['cover']['large']) ? '' : $courseSet['cover']['large'];

            $course['smallPicture'] = $container->get('web.twig.extension')->getFurl($small, 'course.png');
            $course['middlePicture'] = $container->get('web.twig.extension')->getFurl($middle, 'course.png');
            $course['largePicture'] = $container->get('web.twig.extension')->getFurl($large, 'course.png');
            $course['about'] = $self->convertAbsoluteUrl($container->get('request'), $course['about']);
            $course['createdTime'] = date('c', $course['createdTime']);

            $course['teachers'] = [];

            foreach ($course['teacherIds'] as $teacherId) {
                if (isset($teachers[$teacherId])) {
                    $course['teachers'][] = $teachers[$teacherId];
                }
            }

            unset($course['teacherIds']);

            $course['tags'] = TagUtil::buildTags('course-set', $courseSet['id']);
            $course['tags'] = ArrayToolkit::column($course['tags'], 'name');

            $course['priceType'] = $coinSetting['priceType'];
            $course['coinName'] = $coinSetting['name'];

            $course['goals'] = empty($course['goals']) ? [] : $course['goals'];
            $course['audiences'] = empty($course['audiences']) ? [] : $course['audiences'];
            $course['services'] = empty($course['services']) ? [] : $course['services'];
            $course['teacherIds'] = empty($course['teacherIds']) ? [] : $course['teacherIds'];
        }

        return $courses;
    }

    private function convertOldFields($course)
    {
        $convertKeys = [
            'expiryDays' => 'expiryDay',
            'taskNum' => 'lessonNum',
            'creator' => 'userId',
            'tryLookLength' => 'tryLookTime',
            'summary' => 'about',
        ];
        foreach ($convertKeys as $key => $value) {
            $course[$value] = $course[$key];
        }

        return $course;
    }

    private function filledCourseByCourseSet($course, $courseSet)
    {
        $copyKeys = [
            'tags',
            'hitNum',
            'orgCode',
            'orgId',
            'discount',
            'categoryId',
            'recommended',
            'recommendedSeq',
            'recommendedTime',
            'subtitle',
            'discountId',
        ];
        foreach ($copyKeys as $value) {
            $course[$value] = $courseSet[$value];
        }
        $course = CourseTitleUtils::formatTitle($course, $courseSet['title']);

        return $course;
    }

    public function convertAbsoluteUrl($request, $html)
    {
        $self = $this;
        $html = preg_replace_callback(
            '/src=[\'\"]\/(.*?)[\'\"]/',
            function ($matches) use ($self) {
                $path = $matches[1];
                if (0 === strpos($path, 'files')) {
                    $path = str_replace('files/', '', $path);
                }

                $absoluteUrl = $self->coverPath($path, '');

                return "src=\"{$absoluteUrl}\"";
            },
            $html
        );

        return $html;
    }

    public function filterUser($user)
    {
        if (empty($user)) {
            return null;
        }

        $users = $this->filterUsers(
            [
                $user,
            ]
        );

        return current($users);
    }

    public function filterItems($items)
    {
        if (empty($items)) {
            return [];
        }

        $self = $this;
        $container = $this->container;

        $items = array_map(
            function ($item) use ($self, $container) {
                $item['createdTime'] = date('c', $item['createdTime']);

                if (!empty($item['length']) && in_array($item['type'], ['audio', 'video'])) {
                    $item['length'] = $container->get('web.twig.extension')->durationFilter($item['length']);
                } else {
                    $item['length'] = '';
                }

                if (empty($item['content'])) {
                    $item['content'] = '';
                }

                $item['content'] = $self->convertAbsoluteUrl($container->get('request'), $item['content']);

                if (isset($item['status']) && 'published' != $item['status']) {
                    return false;
                }

                return $self->filterTask($item);
            },
            $items
        );

        return array_filter($items);
    }

    public function filterTask($task)
    {
        array_walk($task, function ($value, $key) use (&$task) {
            if (is_numeric($value)) {
                $task[$key] = (string) $value;
            } else {
                $task[$key] = $value;
            }
        });

        return $task;
    }

    public function coverPath($path, $coverPath)
    {
        return $this->container->get('web.twig.extension')->getFurl($path, $coverPath);
    }

    public function filterUsers($users)
    {
        if (empty($users)) {
            return [];
        }

        $container = $this->container;

        $controller = $this;

        return array_map(
            function ($user) use ($container, $controller) {
                $user['smallAvatar'] = $container->get('web.twig.extension')->getFurl(
                    $user['smallAvatar'],
                    'avatar.png'
                );
                $user['mediumAvatar'] = $container->get('web.twig.extension')->getFurl(
                    $user['mediumAvatar'],
                    'avatar.png'
                );
                $user['largeAvatar'] = $container->get('web.twig.extension')->getFurl(
                    $user['largeAvatar'],
                    'avatar-large.png'
                );
                $user['createdTime'] = date('c', $user['createdTime']);

                if (!empty($user['verifiedMobile'])) {
                    $user['verifiedMobile'] = substr_replace($user['verifiedMobile'], '****', 3, 4);
                } else {
                    unset($user['verifiedMobile']);
                }

                if ($controller->isinstalledPlugin('Vip') && $controller->setting('vip.enabled')) {
                    $userVip = $controller->getVipService()->getMemberByUserId($user['id']);

                    if (!empty($userVip)) {
                        $userVipLevel = $controller->getLevelService()->getLevel($userVip['levelId']);

                        $user['vip']['levelId'] = $userVip['levelId'];
                        $user['vip']['vipName'] = $userVipLevel['name'];
                        $user['vip']['VipDeadLine'] = $userVip['deadline'];
                        $user['vip']['seq'] = $userVipLevel['seq'];
                    }
                }

                $userProfile = $controller->getUserService()->getUserProfile($user['id']);
                $user['signature'] = $userProfile['signature'];

                if (isset($user['about'])) {
                    $user['about'] = $controller->convertAbsoluteUrl($controller->request, $userProfile['about']);
                }

                $user['following'] = (string) $controller->getUserService()->findUserFollowingCount($user['id']);
                $user['follower'] = (string) $controller->getUserService()->findUserFollowerCount($user['id']);

                $user['email'] = '****';
                $user['nickname'] = (1 == $user['destroyed']) ? '帐号已注销' : $user['nickname'];

                unset($user['password']);
                unset($user['payPasswordSalt']);
                unset($user['payPassword']);
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
                unset($user['idcard']);
                unset($user['mobile']);
                unset($user['verifiedMobile']);
                unset($user['orgCode']);
                unset($user['orgId']);
                unset($user['registeredWay']);
                unset($user['inviteCode']);
                unset($user['createdTime']);
                unset($user['lockDeadline']);
                unset($user['updatedTime']);
                unset($user['truename']);
                unset($user['emailVerified']);
                unset($user['setup']);
                unset($user['lastPasswordFailTime']);
                unset($user['consecutivePasswordErrorTimes']);
                unset($user['birthday']);

                return $user;
            },
            $users
        );
    }

    public function filterLiveCourses($user, $start, $limit)
    {
        $courses = $this->getCourseService()->findUserLearningCourses($user['id'], $start, $limit);

        $tempCourses = [];
        $tempCourseIds = [];

        foreach ($courses as $key => $course) {
            if (!strcmp($course['type'], 'live')) {
                $tempCourses[$course['id']] = $course;
                $tempCourseIds[] = $course['id'];
            }
        }

        $tempLiveLessons = [];
        $tempCourseIdIndex = 0;
        $tempLessons = [];

        for ($tempCourseIdIndex; $tempCourseIdIndex < sizeof($tempCourseIds); ++$tempCourseIdIndex) {
            $tempLiveLessons = $this->getCourseService()->getCourseLessons($tempCourseIds[$tempCourseIdIndex]);

            if (isset($tempLiveLessons)) {
                $tempLessons[$tempCourseIds[$tempCourseIdIndex]] = $tempLiveLessons;
            }
        }

        $liveLessons = [];

        foreach ($tempCourses as $key => $value) {
            if (isset($liveLessons[$key])) {
                $tempCourses[$key]['liveLessonTitle'] = $liveLessons[$key]['title'];
                $tempCourses[$key]['liveStartTime'] = date('c', $liveLessons[$key]['startTime']);
                $tempCourses[$key]['liveEndTime'] = date('c', $liveLessons[$key]['endTime']);
            } else {
                $tempCourses[$key]['liveLessonTitle'] = '';
                $tempCourses[$key]['liveStartTime'] = '';
                $tempCourses[$key]['liveEndTime'] = '';
            }

            $tempCourses[$key]['tags'] = TagUtil::buildTags('course', $tempCourses[$key]['id']);
            $tempCourses[$key]['tags'] = ArrayToolkit::column($tempCourses[$key]['tags'], 'name');
        }

        return $tempCourses;
    }

    protected function sendRequest($method, $url, $params = [])
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, 'mobile request');

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        if ('POST' == strtoupper($method)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * @todo 要移走，放这里不合适
     */
    public function filterReview($review)
    {
        if (empty($review)) {
            return null;
        }

        $reviews = $this->filterReviews([$review]);

        return current($reviews);
    }

    public function createErrorResponse($request, $name, $message)
    {
        $error = ['error' => ['name' => $name, 'message' => $message]];

        return $this->createJson($request, $error);
    }

    public function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return CourseService
     */
    public function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    public function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    public function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ReviewService
     */
    public function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }

    public function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    public function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    public function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    public function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    public function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    public function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    public function getUserService()
    {
        return $this->createService('User:UserService');
    }

    public function getLogService()
    {
        return $this->createService('System:LogService');
    }

    public function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    public function getLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    public function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    public function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    public function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    public function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    public function getNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    public function getEduCloudService()
    {
        return $this->createService('EduCloud:EduCloudService');
    }

    public function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
    }

    public function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    public function getPushDeviceService()
    {
        return $this->createService('PushDevice:PushDeviceService');
    }

    protected function getDiscountService()
    {
        return $this->createService('Discount:Discount.DiscountService');
    }

    public function redirect($url, $status = 302)
    {
        return parent::redirect($url, $status);
    }

    public function forward($controller, array $path = [], array $query = [])
    {
        return parent::forward($controller, $path, $query);
    }
}
