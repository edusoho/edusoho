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
            array('createdTime', 'DESC'),
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
        $course = $this->getOpenCourseService()->getCourse($id);

        $result = $this->getOpenCourseService()->publishCourse($id);

        if ($course['type'] == 'liveOpen' && !$result['result']) {
            return $this->createJsonResponse(array('message' => '请先设置直播时间'));
        }

        if ($course['type'] == 'open' && !$result['result']) {
            return $this->createJsonResponse(array('message' => '请先创建课时'));
        }

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

    public function recommendListAction(Request $request)
    {
        $conditions                = $request->query->all();
        $conditions['status']      = 'published';
        $conditions['recommended'] = 1;

        $paginator = new Paginator(
            $this->get('request'),
            $this->getOpenCourseService()->searchCourseCount($conditions),
            20
        );

        $courses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('recommendedSeq', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        return $this->render('TopxiaAdminBundle:OpenCourse:recommend-list.html.twig', array(
            'courses'    => $courses,
            'users'      => $users,
            'paginator'  => $paginator,
            'categories' => $categories
        ));
    }

    public function recommendAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);

        $ref    = $request->query->get('ref');
        $filter = $request->query->get('filter');

        if ($request->getMethod() == 'POST') {
            $formData['recommended']     = 1;
            $formData['recommendedSeq']  = $request->request->get('number');
            $formData['recommendedTime'] = time();

            $course = $this->getOpenCourseService()->updateCourse($id, $formData);

            $user = $this->getUserService()->getUser($course['userId']);

            if ($ref == 'recommendList') {
                return $this->render('TopxiaAdminBundle:Course:course-recommend-tr.html.twig', array(
                    'course' => $course,
                    'user'   => $user
                ));
            }

            return $this->renderOpenCourseTr($id, $request);
        }

        return $this->render('TopxiaAdminBundle:Course:course-recommend-modal.html.twig', array(
            'course' => $course,
            'ref'    => $ref,
            'filter' => $filter
        ));
    }

    public function cancelRecommendAction(Request $request, $id, $target)
    {
        $course = $this->getOpenCourseService()->updateCourse($id, array('recommended' => 0, 'recommendedSeq' => 0));

        if ($target == 'recommend_list') {
            return $this->forward('TopxiaAdminBundle:OpenCourse:recommendList', array(
                'request' => $request
            ));
        }

        if ($target == 'open_index') {
            return $this->renderOpenCourseTr($id, $request);
        }
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
        $dataDictionary = array('lessons' => '课时', 'recommend' => '推荐课程', 'members' => '课程成员', 'course' => '课程', 'materials' => '课程文件');

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
