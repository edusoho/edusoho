<?php

namespace AppBundle\Controller\AdminV2;

use AppBundle\Common\CurlToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\QuickEntrance\Service\QuickEntranceService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        $weekAndMonthDate = array('weekDate' => date('Y-m-d', time() - 6 * 24 * 60 * 60), 'monthDate' => date('Y-m-d', time() - 29 * 24 * 60 * 60));

        $userQuickEntrances = $this->getQuickEntranceService()->getEntrancesByUserId($this->getCurrentUser()->getId());

        return $this->render('admin-v2/default/index.html.twig', array(
            'dates' => $weekAndMonthDate,
            'entrances' => $userQuickEntrances,
        ));
    }

    public function questionRemindTeachersAction(Request $request, $courseId, $questionId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $question = $this->getThreadService()->getThread($courseId, $questionId);

        $message = array(
            'courseTitle' => $courseSet['title'],
            'courseId' => $course['id'],
            'threadId' => $question['id'],
            'questionTitle' => strip_tags($question['title']),
        );

        foreach ($course['teacherIds'] as $receiverId) {
            $result = $this->getNotificationService()->notify($receiverId, 'questionRemind', $message);
        }

        return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
    }

    public function feedbackAction(Request $request)
    {
        $site = $this->getSettingService()->get('site');
        $user = $this->getUser();
        $token = CurlToolkit::request('POST', 'http://www.edusoho.com/question/get/token', array());
        $site = array('name' => $site['name'], 'url' => $site['url'], 'token' => $token, 'username' => $user->nickname);
        $site = urlencode(http_build_query($site));

        return $this->redirect('http://www.edusoho.com/question?site='.$site.'');
    }

    public function validateDomainAction(Request $request)
    {
        $result = $this->domainInspect($request);

        if ('ok' == $result['status']) {
            return $this->render('admin-v2/default/domain.html.twig', array('inspectList' => array()));
        }

        return $this->render('admin-v2/default/domain.html.twig', array(
            'inspectList' => array('name' => 'host', 'value' => $result),
        ));
    }

    public function getCloudNoticesAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            $domain = $this->generateUrl('homepage', array(), true);
            $api = CloudAPIFactory::create('root');
            $result = $api->get('/trial/remainDays', array('domain' => $domain));
        }

        return $this->render('admin-v2/default/cloud-notice.html.twig', array(
            'trialTime' => (isset($result)) ? $result : null,
        ));
    }

    private function domainInspect($request)
    {
        $currentHost = $request->server->get('HTTP_HOST');
        $siteSetting = $this->getSettingService()->get('site');
        $settingUrl = $this->generateUrl('admin_v2_school_information');
        $filter = array('http://', 'https://');
        $siteSetting['url'] = rtrim($siteSetting['url']);
        $siteSetting['url'] = rtrim($siteSetting['url'], '/');

        if ($currentHost != str_replace($filter, '', $siteSetting['url'])) {
            return array(
                'status' => 'warning',
                'errorMessage' => ServiceKernel::instance()->trans('admin_v2.domain_error_hint'),
                'except' => $siteSetting['url'],
                'actually' => $currentHost,
                'settingUrl' => $settingUrl,
            );
        }

        return array('status' => 'ok', 'except' => $siteSetting['url'], 'actually' => $currentHost, 'settingUrl' => $settingUrl);
    }

    public function quickEntranceAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
            $quickEntrances = $this->getQuickEntranceService()->updateUserEntrances($this->getCurrentUser()->getId(), $fields);

            return $this->render('admin-v2/default/quick-entrance/index.html.twig', array('entrances' => $quickEntrances));
        }

        $quickEntrances = $this->getQuickEntranceService()->getAllEntrances($this->getCurrentUser()->getId());

        return $this->render('admin-v2/default/quick-entrance/modal.html.twig', array('entranceData' => $quickEntrances));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return QuickEntranceService
     */
    protected function getQuickEntranceService()
    {
        return $this->createService('QuickEntrance:QuickEntranceService');
    }
}
