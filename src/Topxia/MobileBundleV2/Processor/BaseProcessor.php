<?php

namespace Topxia\MobileBundleV2\Processor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\MobileBundleV2\Controller\MobileBaseController;

class BaseProcessor
{
    const API_VERSIN_RANGE = '3.6.0';

    public $formData;
    public $controller;
    public $request;
    protected $delegator;

    private function __construct($controller)
    {
        $this->controller = $controller;
        $this->request = $controller->request;
        $this->formData = $controller->formData;
    }

    public static function getInstance($class, $controller)
    {
        $instance = new $class($controller);
        $processorDelegator = new ProcessorDelegator($instance);
        $instance->setDelegator($processorDelegator);

        return $processorDelegator;
    }

    protected function stopInvoke()
    {
        $this->delegator->stopInvoke();
    }

    protected function getParam($name, $default = null)
    {
        $result = $this->request->get($name, $default);

        return $result;
    }

    protected function filterUsersFiled($users)
    {
        $container = $this->controller->getContainer();

        return array_map(function ($user) use ($container) {
            foreach ($user as $key => $value) {
                if (!in_array($key, array(
                    'id', 'email', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'nickname', 'roles', 'locked', 'about', 'title', ))
                ) {
                    unset($user[$key]);
                }
            }

            $user['smallAvatar'] = $container->get('web.twig.extension')->getFurl($user['smallAvatar'], 'avatar.png');
            $user['mediumAvatar'] = $container->get('web.twig.extension')->getFurl($user['mediumAvatar'], 'avatar.png');
            $user['largeAvatar'] = $container->get('web.twig.extension')->getFurl($user['largeAvatar'], 'avatar-large.png');

            return $user;
        }, $users);
    }

    /**
     * course-large.png.
     */
    protected function coverPic($src, $srcType)
    {
        $container = $this->controller->getContainer();

        return $container->get('web.twig.extension')->getFurl($src, $srcType);
    }

    protected function log($action, $message, $data)
    {
        $this->controller->getLogService()->info(
            MobileBaseController::MOBILE_MODULE,
            $action,
            $message,
            $data
        );
    }

    protected function filterAnnouncements($announcements)
    {
        $controller = $this->controller;

        return array_map(function ($announcement) use ($controller) {
            unset($announcement['userId']);
            unset($announcement['courseId']);
            unset($announcement['updatedTime']);
            $announcement['content'] = $controller->convertAbsoluteUrl($controller->request, $announcement['content']);
            $announcement['createdTime'] = date('c', $announcement['createdTime']);
            $announcement['startTime'] = date('c', $announcement['startTime']);
            $announcement['endTime'] = date('c', $announcement['endTime']);

            return $announcement;
        }, $announcements);
    }

    protected function filterAnnouncement($announcement)
    {
        return $this->filterAnnouncements(array(
            $announcement,
        ));
    }

    protected function setParam($name, $value)
    {
        $this->request->request->set($name, $value);
    }

    public function setDelegator($processorDelegator)
    {
        $this->delegator = $processorDelegator;
    }

    public function getDelegator()
    {
        return $this->delegator;
    }

    public function after()
    {
    }

    public function before()
    {
    }

    protected function getContainer()
    {
        return $this->controller->getContainer();
    }

    protected function getAccountService()
    {
        return ServiceKernel::instance()->createService('Pay:AccountService');
    }

    protected function getAppService()
    {
        return $this->controller->getService('CloudPlatform.AppService');
    }

    protected function getBlockService()
    {
        return $this->controller->getService('Content.BlockService');
    }

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File:UploadFileService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getMessageService()
    {
        return ServiceKernel::instance()->createService('User:MessageService');
    }

    protected function getCouponService()
    {
        return $this->controller->getService('Coupon:CouponService');
    }

    protected function getQuestionService()
    {
        return $this->controller->getService('Question.QuestionService');
    }

    protected function getNotificationService()
    {
        return ServiceKernel::instance()->createService('User:NotificationService');
    }

    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User:TokenService');
    }

    protected function getMobileDeviceService()
    {
        return $this->controller->getService('Util:MobileDeviceService');
    }

    protected function getArticleService()
    {
        return ServiceKernel::instance()->createService('Article:ArticleService');
    }

    protected function getOrderService()
    {
        return $this->controller->getService('Order:OrderService');
    }

    protected function getTagService()
    {
        return $this->controller->getService('Taxonomy:TagService');
    }

    protected function getFileService()
    {
        return $this->controller->getService('Content:FileService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getCourseService()
    {
        return $this->controller->getService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->controller->getService('Course:MemberService');
    }

    protected function getPayCenterService()
    {
        return $this->controller->getService('PayCenter:PayCenterService');
    }

    protected function getTestpaperService()
    {
        return $this->controller->getService('Testpaper:TestpaperService');
    }

    protected function getAnnouncementService()
    {
        return ServiceKernel::instance()->createService('Announcement:AnnouncementService');
    }

    public function getEduCloudService()
    {
        return $this->controller->getService('EduCloud:EduCloudService');
    }

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }

    protected function getUserFieldService()
    {
        return ServiceKernel::instance()->createService('User:UserFieldService');
    }

    public function createErrorResponse($name, $message)
    {
        $error = array(
            'error' => array(
                'name' => $name,
                'message' => $message,
            ),
        );

        return $error;
    }

    protected function previewAsMember($member, $courseId, $user)
    {
        if (empty($member)) {
            return null;
        }

        $userIsTeacher = $this->controller->getCourseMemberService()->isCourseTeacher($courseId, $user['id']);

        if ($userIsTeacher) {
            $member['role'] = 'teacher';
        } else {
            $userIsStudent = $this->controller->getCourseMemberService()->isCourseStudent($courseId, $user['id']);
            $member['role'] = $userIsStudent ? 'student' : null;
        }

        return $member;
    }

    public function array2Map($learnCourses)
    {
        $mapCourses = array();

        if (empty($learnCourses)) {
            return $mapCourses;
        }

        foreach ($learnCourses as $key => $learnCourse) {
            $mapCourses[$learnCourse['id']] = $learnCourse;
        }

        return $mapCourses;
    }

    protected function getSiteInfo($request, $version)
    {
        $site = $this->controller->getSettingService()->get('site', array());
        $mobile = $this->controller->getSettingService()->get('mobile', array());

        if (!empty($mobile['logo'])) {
            $logo = $request->getSchemeAndHttpHost().'/'.$mobile['logo'];
        } else {
            $logo = '';
        }

        $splashs = array();

        for ($i = 1; $i < 6; ++$i) {
            if (!empty($mobile['splash'.$i])) {
                $splashs[] = $request->getSchemeAndHttpHost().'/'.$mobile['splash'.$i];
            }
        }

        return array(
            'name' => $site['name'],
            'url' => $request->getSchemeAndHttpHost().'/mapi_v'.$version,
            'host' => $request->getSchemeAndHttpHost(),
            'logo' => $logo,
            'splashs' => $splashs,
            'apiVersionRange' => array(
                'min' => '1.0.0',
                'max' => self::API_VERSIN_RANGE,
            ),
        );
    }

    protected function guessDeviceFromUserAgent($userAgent)
    {
        $userAgent = strtolower($userAgent);

        $ios = array('iphone', 'ipad', 'ipod');
        foreach ($ios as $keyword) {
            if (strpos($userAgent, $keyword) > -1) {
                return 'ios';
            }
        }

        if (strpos($userAgent, 'Android') > -1) {
            return 'android';
        }

        return 'unknown';
    }

    protected function curlRequest($method, $url, $params = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, 'video request');

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        if (strtoupper($method) == 'POST') {
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
     * 把\t\n转化成空字符串.
     */
    public function filterSpace($content)
    {
        $pattern = '[\\n\\t\\s]';

        return preg_replace($pattern, '', $content);
    }
}
