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

        if (!empty($conditions['tags'])) {
            $tags               = $conditions['tags'];
            $tagNames           = explode(",", $conditions['tags']);
            $tagIds             = ArrayToolkit::column($this->getTagService()->findTagsByNames($tagNames), 'id');
            $conditions['tags'] = $tagIds;
        } else {
            unset($conditions['tags']);
        }

        if (empty($conditions["categoryId"])) {
            unset($conditions["categoryId"]);
        }

        if (empty($conditions["title"])) {
            unset($conditions["title"]);
        }

        if (empty($conditions["creator"])) {
            unset($conditions["creator"]);
        }

        $count = $this->getOpenCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);
        $courses   = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('createdTime', 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaAdminBundle:OpenCourse:index.html.twig', array(
            'tags'       => empty($tags) ? '' : $tags,
            'courses'    => $courses,
            'categories' => $categories,
            'users'      => $users,
            'paginator'  => $paginator,
            'default'    => $default,
            'classrooms' => array(),
            'filter'     => $filter
        ));
    }

    public function publishAction(Request $request, $id)
    {
        $this->getOpenCourseService()->publishCourse($id);

        return $this->renderOpenCourseTr($id, $request);
    }

    public function closeAction(Request $request, $id)
    {
        $this->getOpenCourseService()->closeCourse($id);
        return $this->renderOpenCourseTr($id, $request);
    }

    public function deleteAction(Request $request, $courseId, $type)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isSuperAdmin()) {
            throw $this->createAccessDeniedException('您不是超级管理员！');
        }

        $course = $this->getOpenCourseService()->getCourse($courseId);

        if ($course['status'] == 'published') {
            throw $this->createAccessDeniedException('发布课程，不能删除！');
        }

        $subCourses = $this->getOpenCourseService()->findCoursesByParentIdAndLocked($courseId, 1);

        if (!empty($subCourses)) {
            return $this->createJsonResponse(array('code' => 2, 'message' => '请先删除班级课程'));
        }

        if ($course['status'] == 'draft') {
            $result = $this->getOpenCourseService()->deleteCourse($courseId);
            return $this->createJsonResponse(array('code' => 0, 'message' => '删除课程成功'));
        }

        if ($course['status'] == 'closed') {
            if ($type) {
                $isCheckPassword = $request->getSession()->get('checkPassword');

                if (!$isCheckPassword) {
                    throw $this->createAccessDeniedException('未输入正确的校验密码！');
                }

                $result = $this->getOpenCourseDeleteService()->delete($courseId, $type);

                return $this->createJsonResponse($this->returnDeleteStatus($result, $type));
            }
        }

        return $this->render('TopxiaAdminBundle:OpenCourse:delete.html.twig', array('course' => $course));
    }

    protected function renderOpenCourseTr($courseId, $request)
    {
        $fields  = $request->query->all();
        $course  = $this->getOpenCourseService()->getCourse($courseId);
        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaAdminBundle:OpenCourse:tr.html.twig', array(
            'user'     => $this->getUserService()->getUser($course['userId']),
            'category' => $this->getCategoryService()->getCategory($course['categoryId']),
            'course'   => $course,
            'default'  => $default,
            'filter'   => $fields["filter"]
        ));
    }

    protected function returnDeleteStatus($result, $type)
    {
        $dataDictionary = array('lessons' => '课时', 'recommend' => '推荐课程', 'members' => '课程成员', 'course' => '课程');

        if ($result > 0) {
            $message = $dataDictionary[$type]."数据删除";
            return array('success' => true, 'message' => $message);
        }
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getOpenCourseDeleteService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseDeleteService');
    }
}
