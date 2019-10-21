<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseController extends BaseController
{
    public function indexAction(Request $request, $filter)
    {
        $conditions = $request->query->all();

        if (empty($conditions['categoryId'])) {
            unset($conditions['categoryId']);
        }

        if (empty($conditions['title'])) {
            unset($conditions['title']);
        }

        if (!empty($conditions['creator'])) {
            $users = $this->getUserService()->searchUsers(
                array('nickname' => $conditions['creator']),
                array('createdTime' => 'DESC'),
                0,
                PHP_INT_MAX
            );
            unset($conditions['creator']);

            if ($users) {
                $conditions['userIds'] = ArrayToolkit::column($users, 'id');
            } else {
                $conditions['userIds'] = array(-1);
            }
        }

        $conditions = $this->fillOrgCode($conditions);

        $count = $this->getOpenCourseService()->countCourses($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);
        $courses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $default = $this->getSettingService()->get('default', array());

        return $this->render('admin-v2/teach/open-course/index.html.twig', array(
            'tags' => empty($tags) ? '' : $tags,
            'courses' => $courses,
            'categories' => $categories,
            'users' => $users,
            'paginator' => $paginator,
            'default' => $default,
            'classrooms' => array(),
            'filter' => $filter,
        ));
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
