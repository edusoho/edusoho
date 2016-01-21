<?php
namespace Mooc\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Service\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends BaseController
{
    public function createAction(Request $request)
    {
        $user        = $this->getUserService()->getCurrentUser();
        $userProfile = $this->getUserService()->getUserProfile($user['id']);

        $isLive = $request->query->get('flag');
        $type   = ("isLive" == $isLive) ? 'live' : 'normal';

        if ("isLive" == $isLive) {
            $type = 'live';
        } elseif ("periodic" == $isLive) {
            $type = 'periodic';
        } else {
            $type = 'normal';
        }

        if ('live' == $type) {
            $courseSetting = $this->setting('course', array());

            if (!empty($courseSetting['live_course_enabled'])) {
                $client   = new EdusohoLiveClient();
                $capacity = $client->getCapacity();
            } else {
                $capacity = array();
            }

            if (empty($courseSetting['live_course_enabled'])) {
                return $this->createMessageResponse('info', '请前往后台开启直播,尝试创建！');
            }

            if (empty($capacity['capacity']) && !empty($courseSetting['live_course_enabled'])) {
                return $this->createMessageResponse('info', '请联系EduSoho官方购买直播教室，然后才能开启直播功能！');
            }
        }

        if (false === $this->get('security.context')->isGranted('ROLE_TEACHER')) {
            throw $this->createAccessDeniedException();
        }

        if ($request->getMethod() == 'POST') {
            $course = $request->request->all();
            $course = $this->getCourseService()->createCourse($course);
            return $this->redirect($this->generateUrl('course_manage', array('id' => $course['id'])));
        }

        return $this->render('TopxiaWebBundle:Course:create.html.twig', array(
            'userProfile' => $userProfile,
            'type'        => $type
        ));
    }

    public function nextRoundAction(Request $request, $id)
    {
        $this->checkId($id);

        $course = $this->getCourseService()->getCourse($id);

        if ('periodic' != $course['type']) {
            return $this->createMessageModalResponse('info', '非周期课程不可开设下一期', '周期课程', 3);
        }

        return $this->render('MoocWebBundle:Course:next-round.html.twig', array(
            'course' => $course
        ));
    }

    public function exploreAction(Request $request, $category)
    {
        $conditions          = $request->query->all();
        $categoryArray       = array();
        $conditions['code']  = $category;
        //$conditions['table'] = 'singleCourse';

        if (!empty($conditions['code'])) {
            $categoryArray             = $this->getCategoryService()->getCategoryByCode($conditions['code']);
            $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($categoryArray['id']);
            $categoryIds               = array_merge($childrenIds, array($categoryArray['id']));
            $conditions['categoryIds'] = $categoryIds;
        }

        unset($conditions['code']);

        if (!isset($conditions['fliter'])) {
            $conditions['fliter'] = 'all';
        } elseif ('free' == $conditions['fliter']) {
            $coinSetting = $this->getSettingService()->get("coin");
            $coinEnable  = isset($coinSetting["coin_enabled"]) && 1 == $coinSetting["coin_enabled"];
            $priceType   = "RMB";

            if ($coinEnable && !empty($coinSetting) && array_key_exists("price_type", $coinSetting)) {
                $priceType = $coinSetting["price_type"];
            }

            if ('RMB' == $priceType) {
                $conditions['price'] = '0.00';
            } else {
                $conditions['coinPrice'] = '0.00';
            }
        } elseif ('live' == $conditions['fliter']) {
            $conditions['type'] = 'live';
        }

        $fliter = $conditions['fliter'];
        unset($conditions['fliter']);

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!isset($courseSetting['explore_default_orderBy'])) {
            $courseSetting['explore_default_orderBy'] = 'latest';
        }

        $orderBy = $courseSetting['explore_default_orderBy'];
        $orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
        unset($conditions['orderBy']);

        $conditions['recommended'] = ('recommendedSeq' == $orderBy) ? 1 : null;

        $conditions['parentId'] = 0;
        $conditions['status']   = 'published';
        $paginator              = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions),
            12
        );
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $group = $this->getCategoryService()->getGroupByCode('course');

        if (empty($group)) {
            $categories = array();
        } else {
            $categories = $this->getCategoryService()->getCategoryTree($group['id']);
        }

        if (!$categoryArray) {
            $categoryArrayDescription = array();
        } else {
            $categoryArrayDescription = $categoryArray['description'];
            $categoryArrayDescription = strip_tags($categoryArrayDescription, '');
            $categoryArrayDescription = preg_replace("/ /", "", $categoryArrayDescription);
            $categoryArrayDescription = substr($categoryArrayDescription, 0, 100);
        }

        if (!$categoryArray) {
            $CategoryParent = '';
        } else {
            if (!$categoryArray['parentId']) {
                $CategoryParent = '';
            } else {
                $CategoryParent = $this->getCategoryService()->getCategory($categoryArray['parentId']);
            }
        }

        return $this->render('TopxiaWebBundle:Course:explore.html.twig', array(
            'courses'                  => $courses,
            'category'                 => $category,
            'fliter'                   => $fliter,
            'orderBy'                  => $orderBy,
            'paginator'                => $paginator,
            'categories'               => $categories,
            'consultDisplay'           => true,
            'path'                     => 'course_explore',
            'categoryArray'            => $categoryArray,
            'group'                    => $group,
            'categoryArrayDescription' => $categoryArrayDescription,
            'CategoryParent'           => $CategoryParent
        ));
    }

    public function roundingAction(Request $request, $id)
    {
        $this->checkId($id);
        $course     = $this->getCourseService()->getCourse($id);
        $conditions = $request->request->all();
        $startTime  = strtotime($conditions['startTime']);
        $endTime    = strtotime($conditions['endTime']);

        if ($startTime < $course['endTime']) {
            return $this->createMessageResponse('info', '周期课程开课时间不得早于上一期课程的结课时间', '周期课程', 3, $this->generateUrl('my_teaching_courses'));
        }

        $course['startTime'] = $startTime;
        $course['endTime']   = $endTime;

        $this->getNextRoundService()->rounding($course);

        return $this->redirect($this->generateUrl('my_teaching_courses'));
    }

    protected function getNextRoundService()
    {
        return $this->getServiceKernel()->createService('Mooc:Course.NextRoundService');
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
