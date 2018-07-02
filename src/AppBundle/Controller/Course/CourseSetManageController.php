<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetManageController extends BaseController
{
    public function createAction(Request $request)
    {
        $visibleCourseTypes = $this->getCourseTypes();

        if ($request->isMethod('POST')) {
            $type = $request->request->get('type', '');

            if (empty($type) || empty($visibleCourseTypes[$type])) {
                throw $this->createNotFoundException('未设置课程类型');
            }

            return $this->forward($visibleCourseTypes[$type]['saveAction'], array('request' => $request));
        }

        if (!$this->getCourseSetService()->hasCourseSetManageRole()) {
            throw  $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $userProfile = $this->getUserService()->getUserProfile($user->getId());

        return $this->render(
            'courseset-manage/create.html.twig',
            array(
                'userProfile' => $userProfile,
                'courseTypes' => $visibleCourseTypes,
            )
        );
    }

    public function saveCourseAction(Request $request)
    {
        $data = $request->request->all();
        $courseSet = $this->getCourseSetService()->createCourseSet($data);

        return $this->redirectToRoute(
            'course_set_manage_base',
            array(
                'id' => $courseSet['id'],
            )
        );
    }

    public function indexAction($id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $id,
                    'sideNav' => 'tasks',
                )
            );
        }

        return $this->redirectToRoute(
            'course_set_manage_courses',
            array(
                'courseSetId' => $id,
            )
        );
    }

    public function headerAction($courseSet, $course = null)
    {
        //暂时显示课程的创建者
        $studentNum = $this->getCourseMemberService()->countStudentMemberByCourseSetId($courseSet['id']);
        $couserNum = $this->getCourseService()->countCoursesByCourseSetId($courseSet['id']);

        return $this->render(
            'courseset-manage/header.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $course,
                'studentNum' => $studentNum,
                'couserNum' => $couserNum,
            )
        );
    }

    public function sidebarAction($courseSetId, $curCourse, $courseSideNav)
    {
        $user = $this->getCurrentUser();

        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);

        if (!$user->isAdmin()) {
            $courses = array_filter(
                $courses,
                function ($course) use ($user) {
                    return in_array($user->getId(), $course['teacherIds']);
                }
            );
        }

        if (empty($curCourse)) {
            $curCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSetId);
        }
        if (empty($curCourse) && !empty($courses)) {
            $curCourse = reset($courses);
        }
        $tasks = $this->getTaskService()->findTasksByCourseId($curCourse['id']);

        $hasLiveTasks = ArrayToolkit::some($tasks, function ($task) {
            return 'live' === $task['type'];
        });

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        return $this->render(
            'courseset-manage/sidebar.html.twig',
            array(
                'courseSet' => $courseSet,
                'curCourse' => $curCourse,
                'courses' => $courses,
                'course_side_nav' => $courseSideNav,
                'hasLiveTasks' => $hasLiveTasks,
            )
        );
    }

    //基础信息
    public function baseAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseSetService()->updateCourseSet($id, $data);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('course_set_manage_base', array('id' => $id)));
        }

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $id,
                    'sideNav' => 'base',
                )
            );
        }

        $tags = $this->getTagService()->findTagsByOwner(array(
            'ownerType' => 'course-set',
            'ownerId' => $id,
        ));

        return $this->render(
            'courseset-manage/base.html.twig',
            array(
                'courseSet' => $courseSet,
                'tags' => ArrayToolkit::column($tags, 'name'),
            )
        );
    }

    public function coverAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseSetService()->changeCourseSetCover($id, $data);

            return $this->redirect($this->generateUrl('course_set_manage_cover', array('id' => $id)));
        }

        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        // if ($courseSet['cover']) {
        //     $courseSet['cover'] = json_decode($courseSet['cover'], true);
        // }

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $id,
                    'sideNav' => 'cover',
                )
            );
        }

        return $this->render(
            'courseset-manage/cover.html.twig',
            array(
                'courseSet' => $courseSet,
            )
        );
    }

    public function coverCropAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $courseSet = $this->getCourseSetService()->changeCourseSetCover($courseSet['id'], json_decode($data['images'], true));
            $cover = $this->getWebExtension()->getFpath($courseSet['cover']['large']);

            return $this->createJsonResponse(array('code' => true, 'cover' => $cover));
        }

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $id,
                    'sideNav' => 'cover',
                )
            );
        }

        $fileId = $request->getSession()->get('fileId');

        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render(
            'courseset-manage/cover-crop-modal.html.twig',
            array(
                'courseSet' => $courseSet,
                'pictureUrl' => $pictureUrl,
                'naturalSize' => $naturalSize,
                'scaledSize' => $scaledSize,
            )
        );
    }

    public function deleteAction($id)
    {
        try {
            $this->getCourseSetService()->deleteCourseSet($id);

            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function publishAction($id)
    {
        try {
            $courseSet = $this->getCourseSetService()->getCourseSet($id);

            if ('live' == $courseSet['type']) {
                $course = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSet['id']);

                if (empty($course['maxStudentNum'])) {
                    throw $this->createAccessDeniedException('直播课程发布前需要在计划设置中设置课程人数');
                }

                $this->getCourseService()->publishCourse($course['id']);
            }
            $this->getCourseSetService()->publishCourseSet($id);

            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function closeAction($id)
    {
        try {
            $this->getCourseSetService()->closeCourseSet($id);

            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function syncInfoAction(Request $request, $id)
    {
        $sideNav = $request->query->get('sideNav', '');
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $courses = $this->getCourseService()->findCoursesByCourseSetId($id);
        if (!$courseSet['locked']) {
            $courseSetId = $courseSet['id'];
            $courseId = $courses[0]['id'];
        } else {
            $courseSetId = $courseSet['parentId'];
            $courseId = $courses[0]['parentId'];
        }

        //同步的课程不允许操作的菜单列表
        $lockedCourseSetMenus = array(
            'base' => array(
                'title' => '基本信息',
                'route' => 'course_set_manage_base',
                'params' => array(
                    'id' => $courseSetId,
                ),
            ),
            'detail' => array(
                'title' => '详细信息',
                'route' => 'course_set_manage_detail',
                'params' => array(
                    'id' => $courseSetId,
                ),
            ),
            'cover' => array(
                'title' => '课程封面',
                'route' => 'course_set_manage_cover',
                'params' => array(
                    'id' => $courseSetId,
                ),
            ),
            'question' => array(
                'title' => '题目管理',
                'route' => 'course_set_manage_question',
                'params' => array(
                    'id' => $courseSetId,
                ),
            ),
            'question_plus' => array(
                'title' => '题目导入/导出',
                'route' => 'course_question_plumber',
                'params' => array(
                    'courseSetId' => $courseSetId,
                    'type' => 'import',
                ),
            ),
            'testpaper' => array(
                'title' => '试卷管理',
                'route' => 'course_set_manage_testpaper',
                'params' => array(
                    'id' => $courseSetId,
                ),
            ),
            'files' => array(
                'title' => '课程文件',
                'route' => 'course_set_manage_files',
                'params' => array(
                    'id' => $courseSetId,
                ),
            ),
        );
        $lockedCourseMenus = array(
            'tasks' => array(
                'title' => '计划任务',
                'route' => 'course_set_manage_course_tasks',
                'params' => array('courseSetId' => $courseSetId, 'courseId' => $courseId),
            ),
            'info' => array(
                'title' => '计划设置',
                'route' => 'course_set_manage_course_info',
                'params' => array('courseSetId' => $courseSetId, 'courseId' => $courseId),
            ),
            'replay' => array(
                'title' => '录播管理',
                'route' => 'course_set_manage_course_replay',
                'params' => array('courseSetId' => $courseSetId, 'courseId' => $courseId),
            ),
            'marketing' => array(
                'title' => '营销设置',
                'route' => 'course_set_manage_course_marketing',
                'params' => array('courseSetId' => $courseSetId, 'courseId' => $courseId),
            ),
            'teachers' => array(
                'title' => '教师设置',
                'route' => 'course_set_manage_course_teachers',
                'params' => array('courseSetId' => $courseSetId, 'courseId' => $courseId),
            ),
        );

        if (!empty($lockedCourseSetMenus[$sideNav])) {
            $menuPath = $this->generateUrl($lockedCourseSetMenus[$sideNav]['route'], $lockedCourseSetMenus[$sideNav]['params']);
            $menuTitle = $lockedCourseSetMenus[$sideNav]['title'];
        } elseif (!empty($lockedCourseMenus[$sideNav])) {
            $menuPath = $this->generateUrl($lockedCourseMenus[$sideNav]['route'], $lockedCourseMenus[$sideNav]['params']);
            $menuTitle = $lockedCourseMenus[$sideNav]['title'];
        } else {
            throw new \Exception('Invalid Menu Key');
        }

        if (!$courseSet['locked']) {
            return $this->redirect($menuPath);
        }

        $copyCourseSet = $this->getCourseSetService()->getCourseSet($courseSet['parentId']);

        return $this->render(
            'courseset-manage/locked.html.twig',
            array(
                'id' => $id,
                'sideNav' => $sideNav,
                'courseSet' => $courseSet,
                'copyCourseSet' => $copyCourseSet,
                'menuPath' => $menuPath,
                'menuTitle' => $menuTitle,
            )
        );
    }

    public function unlockConfirmAction($id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        return $this->render(
            'courseset-manage/unlock-confirm.html.twig',
            array(
                'id' => $id,
            )
        );
    }

    public function unlockAction($id)
    {
        try {
            $this->getCourseSetService()->unlockCourseSet($id);

            return $this->createJsonResponse(array('success' => true));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function courseSortAction(Request $request, $courseSetId)
    {
        try {
            $courseIds = $request->request->get('ids');
            $this->getCourseService()->sortCourse($courseSetId, $courseIds);

            return $this->createJsonResponse(true, 200);
        } catch (\Exception $e) {
            return $this->createJsonResponse($e->getMessage(), 500);
        }
    }
    protected function getCourseTypes()
    {
        return $this->get('web.twig.course_extension')->getCourseTypes();
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
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
