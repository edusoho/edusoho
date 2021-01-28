<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\AppService;
use Biz\Content\Service\BlockService;
use Biz\Content\Service\NavigationService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Review\Service\ReviewService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Theme\Service\ThemeService;
use Biz\User\Service\BatchNotificationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!empty($user['id'])) {
            $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);
        }

        $custom = $this->isCustom();
        $friendlyLinks = $this->getNavigationService()->getOpenedNavigationsTreeByType('friendlyLink');

        return $this->render('default/index.html.twig', ['friendlyLinks' => $friendlyLinks, 'custom' => $custom]);
    }

    public function appDownloadAction()
    {
        $meCount = $this->getMeCount();
        $mobileCode = (empty($meCount['mobileCode']) ? 'edusohov3' : $meCount['mobileCode']);

        if ($this->getWebExtension()->isMicroMessenger() && 'edusohov3' == $mobileCode) {
            $url = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.edusoho.kuozhi';
        } else {
            $url = $this->generateUrl('mobile_download', ['from' => 'qrcode', 'code' => $mobileCode], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->render('mobile/app-download.html.twig', [
            'url' => $url,
        ]);
    }

    public function promotedTeacherBlockAction()
    {
        $teacher = $this->getUserService()->findLatestPromotedTeacher(0, 1);

        if ($teacher) {
            $teacher = $teacher[0];
            $teacher = array_merge(
                $teacher,
                $this->getUserService()->getUserProfile($teacher['id'])
            );
        }

        if (isset($teacher['locked']) && '0' !== $teacher['locked']) {
            $teacher = null;
        }

        return $this->render('default/promoted-teacher-block.html.twig', [
            'teacher' => $teacher,
        ]);
    }

    public function latestReviewsBlockAction($number)
    {
        $reviews = $this->getReviewService()->searchReviews(['targetType' => 'course', 'parentId' => 0], ['createdTime' => 'DESC'], 0, $number);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'targetId'));

        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($courses, 'courseSetId'));
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        return $this->render('default/latest-reviews-block.html.twig', [
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
            'courseSets' => $courseSets,
        ]);
    }

    public function topNavigationAction($siteNav = null, $isMobile = false)
    {
        $navigations = $this->getNavigationService()->getOpenedNavigationsTreeByType('top');

        return $this->render('default/top-navigation.html.twig', [
            'navigations' => $navigations,
            'siteNav' => $siteNav,
            'isMobile' => $isMobile,
        ]);
    }

    public function footNavigationAction()
    {
        $navigations = $this->getNavigationService()->findNavigationsByType('foot', 0, 100);

        return $this->render('default/foot-navigation.html.twig', [
            'navigations' => $navigations,
        ]);
    }

    public function friendlyLinkAction()
    {
        $friendlyLinks = $this->getNavigationService()->getOpenedNavigationsTreeByType('friendlyLink');

        return $this->render('default/friend-link.html.twig', [
            'friendlyLinks' => $friendlyLinks,
        ]);
    }

    public function customerServiceAction()
    {
        $customerServiceSetting = $this->getSettingService()->get('customerService', []);

        return $this->render('default/customer-service-online.html.twig', [
            'customerServiceSetting' => $customerServiceSetting,
        ]);
    }

    public function jumpAction(Request $request)
    {
        $courseId = (int) ($request->query->get('id'));

        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $this->getCurrentUser()->id)) {
            $url = $this->generateUrl('live_course_manage_replay', ['id' => $courseId]);
        } else {
            $url = $this->generateUrl('course_show', ['id' => $courseId]);
        }

        $jumpScript = "<script type=\"text/javascript\"> if (top.location !== self.location) {top.location = \"{$url}\";}</script>";

        return new Response($jumpScript);
    }

    public function coursesCategoryAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['parentId'] = 0;
        $categoryId = isset($conditions['categoryId']) ? $conditions['categoryId'] : 0;
        $orderBy = $conditions['orderBy'];
        $courseType = isset($conditions['courseType']) ? $conditions['courseType'] : 'course';

        $config = $this->getThemeService()->getCurrentThemeConfig();

        if (!empty($config['confirmConfig'])) {
            $config = $config['confirmConfig']['blocks']['left'];

            foreach ($config as $template) {
                if (('course-grid-with-condition-index' == $template['code'] && 'course' == $courseType)
                    || ('open-course' == $template['code'] && 'open-course' == $courseType)) {
                    $config = $template;
                }
            }

            $config['orderBy'] = $orderBy;
            $config['categoryId'] = $categoryId;

            return $this->render('default/'.$config['code'].'.html.twig', [
                'config' => $config,
            ]);
        } else {
            return $this->render('default/course-grid-with-condition-index.html.twig', [
                'categoryId' => $categoryId,
                'orderBy' => $orderBy,
            ]);
        }
    }

    public function translateAction(Request $request)
    {
        $locale = $request->query->get('language');
        $targetPath = $request->query->get('_target_path');

        $request->getSession()->set('_locale', $locale);

        $currentUser = $this->getCurrentUser();

        if ($currentUser->isLogin()) {
            $this->getUserService()->updateUserLocale($currentUser['id'], $locale);
        }

        return $this->redirectSafely($targetPath);
    }

    public function clientTimeCheckAction(Request $request)
    {
        $clientTime = $request->request->get('clientTime');
        $clientTime = strtotime($clientTime);

        if ($clientTime < time()) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse(true);
    }

    private function getMeCount()
    {
        $meCount = $this->setting('meCount', false);
        if (false === $meCount) {
            //判断是否是定制用户
            $result = CloudAPIFactory::create('leaf')->get('/me');
            $this->getSettingService()->set('meCount', $result);
        }
        $meCount = $this->setting('meCount');

        return $meCount;
    }

    private function isCustom()
    {
        $result = $this->getMeCount();

        return isset($result['hasMobile']) ? $result['hasMobile'] : 0;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return NavigationService
     */
    protected function getNavigationService()
    {
        return $this->getBiz()->service('Content:NavigationService');
    }

    /**
     * @return BlockService
     */
    protected function getBlockService()
    {
        return $this->getBiz()->service('Content:BlockService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getBiz()->service('Review:ReviewService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return BatchNotificationService
     */
    protected function getBatchNotificationService()
    {
        return $this->getBiz()->service('User:BatchNotificationService');
    }

    /**
     * @return ThemeService
     */
    protected function getThemeService()
    {
        return $this->getBiz()->service('Theme:ThemeService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
