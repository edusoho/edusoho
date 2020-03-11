<?php

namespace Topxia\MobileBundleV2\Processor;

use AppBundle\Util\CdnUrl;
use Biz\Announcement\Service\AnnouncementService;
use Biz\Article\Service\ArticleService;
use Biz\CloudPlatform\Service\AppService;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\Content\Service\BlockService;
use Biz\Content\Service\FileService;
use Biz\Coupon\Service\CouponService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\File\Service\UploadFileService;
use Biz\Question\Service\QuestionService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\TagService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\MessageService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserFieldService;
use Biz\User\Service\UserService;
use Biz\Util\Service\MobileDeviceService;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Pay\Service\AccountService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\MobileBundleV2\Controller\MobileBaseController;
use Biz\System\Service\H5SettingService;

class BaseProcessor
{
    const API_VERSIN_RANGE = '3.6.0';

    public $formData;
    /**
     * @var MobileBaseController
     */
    public $controller;
    /**
     * @var Request
     */
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
                    'id', 'email', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'nickname', 'roles', 'locked', 'about', 'title', 'destroyed', ))
                ) {
                    unset($user[$key]);
                }
            }

            $user['smallAvatar'] = $container->get('web.twig.extension')->getFurl($user['smallAvatar'], 'avatar.png');
            $user['mediumAvatar'] = $container->get('web.twig.extension')->getFurl($user['mediumAvatar'], 'avatar.png');
            $user['largeAvatar'] = $container->get('web.twig.extension')->getFurl($user['largeAvatar'], 'avatar-large.png');
            $user['nickname'] = ($user['destroyed'] == 1) ? '帐号已注销' : $user['nickname'];

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

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->controller->getService('Pay:AccountService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->controller->getService('CloudPlatform.AppService');
    }

    /**
     * @return BlockService
     */
    protected function getBlockService()
    {
        return $this->controller->getService('Content.BlockService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->controller->getService('File:UploadFileService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->controller->getService('User:UserService');
    }

    /**
     * @return MessageService
     */
    protected function getMessageService()
    {
        return $this->controller->getService('User:MessageService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->controller->getService('Coupon:CouponService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->controller->getService('Question.QuestionService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->controller->getService('User:NotificationService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->controller->getService('User:TokenService');
    }

    /**
     * @return MobileDeviceService
     */
    protected function getMobileDeviceService()
    {
        return $this->controller->getService('Util:MobileDeviceService');
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->controller->getService('Article:ArticleService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->controller->getService('Order:OrderService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->controller->getService('Taxonomy:TagService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->controller->getService('Content:FileService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->controller->getService('System:SettingService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->controller->getService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->controller->getService('Course:MemberService');
    }

    /**
     * @todo 不存在的service,检查一下调用方，没用的就删除掉
     */
    protected function getPayCenterService()
    {
        return $this->controller->getService('PayCenter:PayCenterService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->controller->getService('Testpaper:TestpaperService');
    }

    /**
     * @return AnnouncementService
     */
    protected function getAnnouncementService()
    {
        return $this->controller->getService('Announcement:AnnouncementService');
    }

    /**
     * @return EduCloudService
     */
    public function getEduCloudService()
    {
        return $this->controller->getService('EduCloud:EduCloudService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->controller->getService('System:LogService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->controller->getService('User:UserFieldService');
    }

    /**
     * @return H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->controller->getService('System:H5SettingService');
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
            $logo = $this->getBaseUrl().'/'.$mobile['logo'];
        } else {
            $logo = '';
        }

        $splashs = array();

        for ($i = 1; $i < 6; ++$i) {
            if (!empty($mobile['splash'.$i])) {
                $splashs[] = $this->getBaseUrl().'/'.$mobile['splash'.$i];
            }
        }

        return array(
            'name' => $site['name'],
            'url' => $request->getSchemeAndHttpHost().'/mapi_v'.$version,
            'host' => $request->getSchemeAndHttpHost(),
            'logo' => $logo,
            'splashs' => $splashs,
            'appDiscoveryVersion' => $this->getH5SettingService()->getAppDiscoveryVersion(),
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

    protected function getBaseUrl($type = 'default')
    {
        $cdnUrl = $this->getCdn($type);

        if (!empty($cdnUrl)) {
            return $this->request->getScheme().':'.$cdnUrl;
        }

        return $this->request->getSchemeAndHttpHost();
    }

    protected function getCdn($type = 'default')
    {
        $cdn = new CdnUrl();

        return $cdn->get($type);
    }

    protected function getScheme()
    {
        return $this->request->getScheme();
    }

    protected function isAbsoluteUrl($url)
    {
        return false !== strpos($url, '://') || '//' === substr($url, 0, 2);
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
