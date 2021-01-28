<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\CourseException;
use Biz\OpenCourse\OpenCourseException;
use Biz\User\UserException;
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

        return $this->render('admin/open-course/index.html.twig', array(
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

    public function publishAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);

        $result = $this->getOpenCourseService()->publishCourse($id);

        if ('liveOpen' == $course['type'] && !$result['result']) {
            return $this->createJsonResponse(array('message' => '请先设置直播时间'));
        }

        if ('open' == $course['type'] && !$result['result']) {
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
        $currentUser = $this->getUser();

        if (!$currentUser->isSuperAdmin()) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $course = $this->getOpenCourseService()->getCourse($courseId);

        if ('published' == $course['status']) {
            $this->createNewException(CourseException::FORBIDDEN_DELETE_PUBLISHED());
        }

        if ('draft' == $course['status']) {
            $this->getOpenCourseService()->deleteCourse($courseId);

            return $this->createJsonResponse(array('code' => 0, 'message' => '删除课程成功'));
        }

        if ('closed' == $course['status']) {
            if ($type) {
                $isCheckPassword = $request->getSession()->get('checkPassword');

                if (!$isCheckPassword) {
                    $this->createNewException(OpenCourseException::CHECK_PASSWORD_REQUIRED());
                }

                $result = $this->getOpenCourseDeleteService()->delete($courseId, $type);

                return $this->createJsonResponse($this->returnDeleteStatus($result, $type));
            }
        }

        return $this->render('admin/open-course/delete.html.twig', array('course' => $course));
    }

    public function recommendListAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['recommended'] = 1;

        $paginator = new Paginator(
            $this->get('request'),
            $this->getOpenCourseService()->countCourses($conditions),
            20
        );

        $courses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('recommendedSeq' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        return $this->render('admin/open-course/recommend-list.html.twig', array(
            'courses' => $courses,
            'users' => $users,
            'paginator' => $paginator,
            'categories' => $categories,
        ));
    }

    public function recommendAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->getCourse($id);

        $ref = $request->query->get('ref');
        $filter = $request->query->get('filter');

        if ('POST' == $request->getMethod()) {
            $formData['recommended'] = 1;
            $formData['recommendedSeq'] = $request->request->get('number');
            $formData['recommendedTime'] = time();

            $course = $this->getOpenCourseService()->updateCourse($id, $formData);

            $user = $this->getUserService()->getUser($course['userId']);

            if ('recommendList' == $ref) {
                return $this->render('admin/open-course/recommend-tr.html.twig', array(
                    'course' => $course,
                    'user' => $user,
                ));
            }

            return $this->renderOpenCourseTr($id, $request);
        }

        return $this->render('admin/open-course/recommend-modal.html.twig', array(
            'course' => $course,
            'ref' => $ref,
            'filter' => $filter,
        ));
    }

    public function cancelRecommendAction(Request $request, $id, $target)
    {
        $course = $this->getOpenCourseService()->updateCourse($id, array('recommended' => 0, 'recommendedSeq' => 0));

        if ('recommend_list' == $target) {
            return $this->forward('AppBundle:Admin/OpenCourse:recommendList', array(
                'request' => $request,
            ));
        }

        if ('open_index' == $target) {
            return $this->renderOpenCourseTr($id, $request);
        }
    }

    protected function renderOpenCourseTr($courseId, $request)
    {
        $fields = $request->query->all();
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $default = $this->getSettingService()->get('default', array());

        return $this->render('admin/open-course/tr.html.twig', array(
            'user' => $this->getUserService()->getUser($course['userId']),
            'category' => $this->getCategoryService()->getCategory($course['categoryId']),
            'course' => $course,
            'default' => $default,
            'filter' => $fields['filter'],
        ));
    }

    protected function returnDeleteStatus($result, $type)
    {
        $dataDictionary = array('lessons' => '课时', 'recommend' => '推荐课程', 'members' => '课程成员', 'course' => '课程', 'materials' => '课程文件');

        if ($result > 0) {
            $message = $dataDictionary[$type].'数据删除';

            return array('success' => true, 'message' => $message);
        }
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getOpenCourseDeleteService()
    {
        return $this->createService('OpenCourse:OpenCourseDeleteService');
    }
}
