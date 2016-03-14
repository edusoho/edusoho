<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseController extends BaseController
{
    public function indexAction(Request $request, $filter)
    {
        $conditions = $request->query->all();

        $conditions['types'] = array('open', 'liveOpen');

        if (isset($conditions["categoryId"]) && $conditions["categoryId"] == "") {
            unset($conditions["categoryId"]);
        }

        if (isset($conditions["title"]) && $conditions["title"] == "") {
            unset($conditions["title"]);
        }

        if (isset($conditions["creator"]) && $conditions["creator"] == "") {
            unset($conditions["creator"]);
        }

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);
        $courses   = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaAdminBundle:OpenCourse:index.html.twig', array(
            'courses'    => $courses,
            'categories' => $categories,
            'users'      => $users,
            'paginator'  => $paginator,
            'default'    => $default,
            'classrooms' => array(),
            'filter'     => $filter
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
