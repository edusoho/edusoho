<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Biz\Course\CourseSetException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\Service\SyncEventService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;

class CourseSetManageController extends BaseController
{
    public function createAction(Request $request)
    {
        if (!$this->canCreateCourse()) {
            return $this->createMessageResponse('info', $this->trans('exception.courseset.forbidden_create'));
        }

        $visibleCourseTypes = $this->getCourseTypes();

        if ($request->isMethod('POST')) {
            $type = $request->request->get('type', '');

            if (empty($type) || empty($visibleCourseTypes[$type])) {
                $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
            }

            return $this->forward($visibleCourseTypes[$type]['saveAction'], ['request' => $request]);
        }

        if (!$this->getCourseSetService()->hasCourseSetManageRole()) {
            $this->createNewException(CourseSetException::FORBIDDEN_MANAGE());
        }

        return $this->render(
            'courseset-manage/create.html.twig',
            [
                'userProfile' => $this->getUserService()->getUserProfile($this->getUser()->getId()),
                'courseTypes' => $visibleCourseTypes,
                'defaultType' => $request->query->get('default', CourseSetService::NORMAL_TYPE),
            ]
        );
    }

    public function saveCourseAction(Request $request)
    {
        $data = $request->request->all();
        $courseSet = $this->getCourseSetService()->createCourseSet($data);

        return $this->redirectToRoute(
            'course_set_manage_base',
            [
                'id' => $courseSet['id'],
            ]
        );
    }

    public function indexAction($id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                [
                    'id' => $id,
                    'sideNav' => 'tasks',
                ]
            );
        }

        return $this->redirectToRoute(
            'course_set_manage_courses',
            [
                'courseSetId' => $id,
            ]
        );
    }

    public function headerAction($courseSet, $course = null, $foldType = 0)
    {
        //暂时显示课程的创建者
        $studentNum = $this->getCourseMemberService()->countStudentMemberByCourseSetId($courseSet['id']);
        $couserNum = $this->getCourseService()->countCoursesByCourseSetId($courseSet['id']);

        return $this->render(
            'courseset-manage/header.html.twig',
            [
                'courseSet' => $courseSet,
                'course' => $course,
                'studentNum' => $studentNum,
                'couserNum' => $couserNum,
                'foldType' => $foldType,
            ]
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

        $courses = ArrayToolkit::index($courses, 'id');

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
            [
                'courseSet' => $courseSet,
                'curCourse' => $curCourse,
                'courses' => $courses,
                'course_side_nav' => $courseSideNav,
                'hasLiveTasks' => $hasLiveTasks,
            ]
        );
    }

    //基础信息
    public function baseAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        if (in_array($courseSet['type'], ['live', 'reservation']) || !empty($courseSet['parentId'])) {
            return $this->redirectToRoute(
                'course_set_manage_course_info',
                [
                    'courseSetId' => $id,
                    'courseId' => $courseSet['defaultCourseId'],
                ]
            );
        }
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getCourseSetService()->updateCourseSet($id, $data);

            return $this->createJsonResponse(true);
        }

        if ('supplier' == $courseSet['platform']) {
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $product = $this->getS2B2CProductService()->getProductBySupplierIdAndLocalResourceIdAndType($s2b2cConfig['supplierId'], $courseSet['id'], 'course_set');
            $this->getSyncEventService()->confirmByEvents($product['remoteProductId'], [SyncEventService::EVENT_CLOSE_COURSE]);
        }

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                [
                    'id' => $id,
                    'sideNav' => 'base',
                ]
            );
        }

        $tags = $this->getTagService()->findTagsByOwner([
            'ownerType' => 'course-set',
            'ownerId' => $id,
        ]);

        return $this->render(
            'courseset-manage/base.html.twig',
            [
                'courseSet' => $courseSet,
                'isCoursesSummaryEmpty' => $this->getCourseService()->isCourseSetCoursesSummaryEmpty($courseSet['id']),
                'tags' => ArrayToolkit::column($tags, 'name'),
            ]
        );
    }

    public function coverCropAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                [
                    'id' => $id,
                    'sideNav' => 'cover',
                ]
            );
        }

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $courseSet = $this->getCourseSetService()->changeCourseSetCover($courseSet['id'], $data['images']);
            $cover = $this->getWebExtension()->getFpath($courseSet['cover']['large']);

            return $this->createJsonResponse(['image' => $cover]);
        }

        return $this->render('courseset-manage/cover-crop-modal.html.twig');
    }

    public function deleteAction($id)
    {
        $this->getCourseSetService()->deleteCourseSet($id);

        return $this->createJsonResponse(['success' => true]);
    }

    public function publishAction($id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        if ('supplier' == $courseSet['platform']) {
            $this->getCourseProductService()->checkCourseSetStatus($id);
        }

        if ('live' == $courseSet['type']) {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSet['id']);

            if (empty($course['maxStudentNum'])) {
                $this->createNewException(CourseSetException::LIVE_STUDENT_NUM_REQUIRED());
            }

            $this->getCourseService()->publishCourse($course['id']);
        }
        $this->getCourseSetService()->publishCourseSet($id);

        return $this->createJsonResponse(['success' => true]);
    }

    public function closeAction($id)
    {
        $this->getCourseSetService()->closeCourseSet($id);

        return $this->createJsonResponse(['success' => true]);
    }

    public function syncInfoAction(Request $request, $id)
    {
        $sideNav = $request->query->get('sideNav', '');
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $courses = $this->getCourseService()->findCoursesByCourseSetId($id);
        $course = empty($courses) ? [] : reset($courses);
        if (!$courseSet['locked']) {
            $courseSetId = $courseSet['id'];
            $courseId = $course['id'];
        } else {
            $courseSetId = $courseSet['parentId'];
            $courseId = $course['parentId'];
        }

        //同步的课程不允许操作的菜单列表
        $lockedCourseSetMenus = [
            'base' => [
                'title' => '基本信息',
                'route' => 'course_set_manage_base',
                'params' => [
                    'id' => $courseSetId,
                ],
            ],
            'detail' => [
                'title' => '详细信息',
                'route' => 'course_set_manage_detail',
                'params' => [
                    'id' => $courseSetId,
                ],
            ],
            'cover' => [
                'title' => '课程封面',
                'route' => 'course_set_manage_cover',
                'params' => [
                    'id' => $courseSetId,
                ],
            ],
            'question' => [
                'title' => '题目管理',
                'route' => 'course_set_manage_question',
                'params' => [
                    'id' => $courseSetId,
                ],
            ],
            'testpaper' => [
                'title' => '试卷管理',
                'route' => 'course_set_manage_testpaper',
                'params' => [
                    'id' => $courseSetId,
                ],
            ],
            'files' => [
                'title' => '文件管理',
                'route' => 'course_set_manage_files',
                'params' => [
                    'id' => $courseSetId,
                ],
            ],
        ];
        $lockedCourseMenus = [
            'tasks' => [
                'title' => '计划任务',
                'route' => 'course_set_manage_course_tasks',
                'params' => ['courseSetId' => $courseSetId, 'courseId' => $courseId],
            ],
            'info' => [
                'title' => '计划设置',
                'route' => 'course_set_manage_course_info',
                'params' => ['courseSetId' => $courseSetId, 'courseId' => $courseId],
            ],
            'replay' => [
                'title' => '录播管理',
                'route' => 'course_set_manage_course_replay',
                'params' => ['courseSetId' => $courseSetId, 'courseId' => $courseId],
            ],
            'marketing' => [
                'title' => '营销设置',
                'route' => 'course_set_manage_course_marketing',
                'params' => ['courseSetId' => $courseSetId, 'courseId' => $courseId],
            ],
            'teachers' => [
                'title' => '教师设置',
                'route' => 'course_set_manage_course_teachers',
                'params' => ['courseSetId' => $courseSetId, 'courseId' => $courseId],
            ],
            'live-statistics' => [
                'title' => '直播统计',
                'route' => 'course_set_manage_live_statistics',
                'params' => ['courseSetId' => $courseSetId, 'courseId' => $courseId],
            ],
        ];

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

        $template = $this->getTemplate($sideNav);

        return $this->render(
            $template,
            [
                'id' => $id,
                'sideNav' => $sideNav,
                'courseSet' => $courseSet,
                'copyCourseSet' => $copyCourseSet,
                'menuPath' => $menuPath,
                'menuTitle' => $menuTitle,
                'course' => $course,
            ]
        );
    }

    public function unlockConfirmAction($id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        return $this->render(
            'courseset-manage/unlock-confirm.html.twig',
            [
                'id' => $id,
            ]
        );
    }

    public function unlockAction($id)
    {
        $this->getCourseSetService()->unlockCourseSet($id);

        return $this->createJsonResponse(['success' => true]);
    }

    public function courseSortAction(Request $request, $courseSetId)
    {
        $courseIds = $request->request->get('ids');
        $this->getCourseService()->sortCourse($courseSetId, $courseIds);

        return $this->createJsonResponse(true, 200);
    }

    protected function getTemplate($sideNav)
    {
        if (in_array($sideNav, ['files', 'testpaper', 'question'])) {
            return 'courseset-manage/locked-item.html.twig';
        } else {
            return 'courseset-manage/locked.html.twig';
        }
    }

    protected function getCourseTypes()
    {
        return $this->get('web.twig.course_extension')->getCourseTypes();
    }

    protected function canCreateCourse()
    {
        $behaviourPermissions = $this->getS2b2cFacedService()->getBehaviourPermissions();

        return isset($behaviourPermissions['canAddCourse']) ? $behaviourPermissions['canAddCourse'] : true;
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2b2cFacedService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
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

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return SyncEventService
     */
    protected function getSyncEventService()
    {
        return $this->createService('S2B2C:SyncEventService');
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }
}
