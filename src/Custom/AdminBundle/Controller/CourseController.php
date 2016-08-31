<?php
namespace Custom\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;

class CourseController extends BaseController
{
    public function indexAction(Request $request, $filter)
    {
        $user = $this->getCurrentUser();
        $conditions = $request->query->all();
        if ($filter == 'normal') {
            $conditions["parentId"] = 0;
        }

        if ($filter == 'classroom') {
            $conditions["parentId_GT"] = 0;
        }

        if ($filter == 'vip') {
            $conditions['vipLevelIdGreaterThan'] = 1;
            $conditions["parentId"]              = 0;
        }

        if (isset($conditions["categoryId"]) && $conditions["categoryId"] == "") {
            unset($conditions["categoryId"]);
        }

        if (isset($conditions["status"]) && $conditions["status"] == "") {
            unset($conditions["status"]);
        }

        if (isset($conditions["title"]) && $conditions["title"] == "") {
            unset($conditions["title"]);
        }

        if (isset($conditions["creator"]) && $conditions["creator"] == "") {
            unset($conditions["creator"]);
        }

        if (!(isset($conditions['orgCode']) && strlen($conditions['orgCode']) < strlen($user['orgCode']) && strpos($user['orgCode'], $conditions['orgCode']) == 0)) {
            $conditions = $this->fillOrgCode($conditions);
        }

        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1 && $coinSetting['cash_model'] == 'currency';

        if (isset($conditions["chargeStatus"]) && $conditions["chargeStatus"] == "free") {
            $conditions['price'] = '0.00';
        }

        if (isset($conditions["chargeStatus"]) && $conditions["chargeStatus"] == "charge") {
            $conditions['price_GT'] = '0.00';
        }

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);
        $courses   = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $classrooms = array();
        $vips       = array();
        if ($filter == 'classroom') {
            $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds(ArrayToolkit::column($courses, 'id'));
            $classrooms = ArrayToolkit::index($classrooms, 'courseId');

            foreach ($classrooms as $key => $classroom) {
                $classroomInfo                      = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        } elseif ($filter == 'vip') {
            if ($this->isPluginInstalled('Vip')) {
                $vips = $this->getVipLevelService()->searchLevels(array(), 0, PHP_INT_MAX);
                $vips = ArrayToolkit::index($vips, 'id');
            }
        }

        $orgParentIds = explode('.', substr($user['orgCode'], 0, strlen($user['orgCode'])-1));
        array_pop($orgParentIds);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!isset($courseSetting['live_course_enabled'])) {
            $courseSetting['live_course_enabled'] = "";
        }

        $default = $this->getSettingService()->get('default', array());

        return $this->render('CustomAdminBundle:Course:index.html.twig', array(
            'conditions'     => $conditions,
            'courses'        => $courses,
            'users'          => $users,
            'categories'     => $categories,
            'paginator'      => $paginator,
            'liveSetEnabled' => $courseSetting['live_course_enabled'],
            'default'        => $default,
            'classrooms'     => $classrooms,
            'filter'         => $filter,
            'vips'           => $vips,
            'orgParentIds'   => $orgParentIds
        ));
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getVipLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }
}