<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExportHelp;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseDeleteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\SyncEventService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Taxonomy\Service\Impl\TagServiceImpl;
use Biz\User\UserException;
use Biz\Visualization\Service\ActivityLearnDataService;
use Biz\Visualization\Service\CoursePlanLearnDataDailyStatisticsService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\CourseVipRightSupplier;
use VipPlugin\Biz\Vip\Service\LevelService;

class CourseSetController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['excludeTypes'] = ['reservation'];
        $filter = empty($conditions['filter']) ? 'normal' : $conditions['filter'];
        unset($conditions['filter']);
        $conditions = $this->filterCourseSetConditions($filter, $conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($courseSets, $coursesCount, $classroomCourses) = $this->findRelatedOptions($filter, $courseSets);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));
        $courseSetStatusNum = $this->getDifferentCourseSetsNum($conditions);
        $courseSets = $this->buildCourseSetTags($courseSets);

        /**
         * S2B2C
         */
        $notifies = ArrayToolkit::index($this->getSyncEventService()->findNotifyByCourseSetIds(ArrayToolkit::column($courseSets, 'id')), 'courseSetId');

        return $this->render(
            'admin-v2/teach/course-set/index.html.twig',
            [
                'courseSets' => $courseSets,
                'users' => $users,
                'categories' => $categories,
                'paginator' => $paginator,
                'classrooms' => $classroomCourses,
                'filter' => $filter,
                'tag' => empty($conditions['tagId']) ? [] : $this->getTagService()->getTag($conditions['tagId']),
                'courseSetStatusNum' => $courseSetStatusNum,
                'coursesCount' => $coursesCount,
                'notifies' => $notifies,
            ]
        );
    }

    public function closeAction(Request $request, $id)
    {
        $this->getCourseSetService()->closeCourseSet($id);

        return $this->renderCourseTr($id, $request);
    }

    /*
    code 状态编号
    1:　删除班级课程
    2: 移除班级课程
    0: 删除未发布课程成功
     */
    public function deleteAction(Request $request, $id)
    {
        $currentUser = $this->getUser();

        if (!$currentUser->hasPermission('admin_v2_course_set_delete')) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        $classroomRef = $this->getClassroomService()->getClassroomCourseByCourseSetId($id);
        if (!empty($classroomRef)) {
            return $this->createJsonResponse(['code' => 2, 'message' => '请先从班级管理将本课程移除']);
        }
        $subCourses = $this->getCourseSetService()->findCourseSetsByParentIdAndLocked($id, 1);
        if (!empty($subCourses) || ($courseSet['parentId'] && 1 == $courseSet['locked'])) {
            return $this->createJsonResponse(['code' => 2, 'message' => '请先删除班级课程']);
        }

        if ('draft' == $courseSet['status']) {
            $this->getCourseSetService()->deleteCourseSet($id);

            return $this->createJsonResponse(['code' => 0, 'message' => '删除课程成功']);
        }

        $isCheckPasswordLifeTime = $request->getSession()->get('checkPassword');
        if (!$isCheckPasswordLifeTime || $isCheckPasswordLifeTime < time()) {
            return $this->render('admin-v2/teach/course/delete.html.twig', ['deleteUrl' => $this->generateUrl('admin_v2_course_set_delete', ['id' => $courseSet['id']])]);
        }

        $this->getCourseSetService()->deleteCourseSet($id);

        return $this->createJsonResponse(['code' => 0, 'message' => '删除课程成功']);
    }

    public function checkEsProductCanDeleteAction(Request $request, $id)
    {
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByCourseSetId($id), 'id');
        $status = $this->getProductMallGoodsRelationService()->checkEsProductCanDelete($courseIds, 'course');
        return $this->createJsonResponse(['status' => $status]);


    }

    //todo 和CourseController 有一样的
    public function checkPasswordAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $password = $request->request->get('password');
            $currentUser = $this->getUser();
            $password = $this->getPasswordEncoder()->encodePassword($password, $currentUser->salt);

            if ($password == $currentUser->password) {
                $response = ['success' => true, 'message' => '密码正确'];
                $request->getSession()->set('checkPassword', true);
            } else {
                $response = ['success' => false, 'message' => '密码错误'];
            }

            return $this->createJsonResponse($response);
        }
        $this->createNewException(CommonException::NOT_ALLOWED_METHOD());
    }

    public function publishAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        //检查课程状态
        if ('supplier' == $courseSet['platform']) {
            $this->getCourseProductService()->checkCourseSetStatus($courseSet['id']);
        }

        if ('live' == $courseSet['type']) {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSet['id']);

            if ($course['maxStudentNum'] < 0) {
                return $this->createJsonResponse([
                    'success' => false,
                    'message' => '直播课程发布前需要在计划设置中设置课程人数',
                ]);
            }
        }

        $this->getCourseSetService()->publishCourseSet($id);

        return $this->renderCourseTr($id, $request);
    }

    public function recommendAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        $ref = $request->query->get('ref');
        $filter = $request->query->get('filter');

        if ('POST' == $request->getMethod()) {
            $number = $request->request->get('number');

            $courseSet = $this->getCourseSetService()->recommendCourse($id, $number);

            $user = $this->getUserService()->getUser($courseSet['creator']);

            if ('recommendList' == $ref) {
                return $this->render(
                    'admin-v2/teach/course-set/course-recommend-tr.html.twig',
                    [
                        'courseSet' => $courseSet,
                        'user' => $user,
                    ]
                );
            }

            return $this->renderCourseTr($id, $request);
        }

        return $this->render(
            'admin-v2/teach/course-set/course-recommend-modal.html.twig',
            [
                'courseSet' => $courseSet,
                'ref' => $ref,
                'filter' => $filter,
            ]
        );
    }

    public function cancelRecommendAction(Request $request, $id, $target)
    {
        $this->getCourseSetService()->cancelRecommendCourse($id);

        if ('recommend_list' == $target) {
            return $this->createJsonResponse(['success' => 1]);
        }

        if ('normal_index' == $target) {
            return $this->renderCourseTr($id, $request);
        }

        $this->createNewException(CommonException::ERROR_PARAMETER());
    }

    public function recommendListAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['recommended'] = 1;

        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            'recommendedSeq',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        return $this->render(
            'admin-v2/teach/course-set/course-recommend-list.html.twig',
            [
                'courseSets' => $courseSets,
                'users' => $users,
                'paginator' => $paginator,
                'categories' => $categories,
            ]
        );
    }

    public function dataListAction(Request $request, $filter)
    {
        $conditions = $request->query->all();

        if ('normal' == $filter) {
            $conditions['parentId'] = 0;
            $conditions['excludeTypes'] = ['reservation'];
            $conditions = $this->filterCourseSetType($conditions);
        }

        if ('classroom' == $filter) {
            $conditions['parentId_GT'] = 0;
        }

        $conditions = $this->fillOrgCode($conditions);

        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $paginator = new Paginator($this->get('request'), $count, 20);

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            ['id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $classrooms = [];

        if ('classroom' == $filter) {
            $classrooms = $this->getClassroomService()->findClassroomsByCourseSetIds(
                ArrayToolkit::column($courseSets, 'id')
            );
            $classrooms = ArrayToolkit::index($classrooms, 'courseSetId');

            foreach ($classrooms as $key => $classroom) {
                $classroomInfo = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        }

        $courseSets = $this->statsCourseSetData($courseSets);

        return $this->render(
            'admin-v2/teach/course-set/data.html.twig',
            [
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'filter' => $filter,
                'classrooms' => $classrooms,
            ]
        );
    }

    public function detailDataAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($id);
        $courses = $this->removeUnpublishAndNonDefaultCourses($courses);
        $courseId = $request->query->get('courseId');

        if (empty($courseId)) {
            $courseId = $courses[0]['id'];
        }

        $count = $this->getMemberService()->countMembers(['courseId' => $courseId, 'role' => 'student']);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $students = $this->getMemberService()->searchMembers(
            ['courseId' => $courseId, 'role' => 'student'],
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $studentIds = ArrayToolkit::column($students, 'userId');
        $userProfilesAndApprovedApprovals = $this->makeUserProfilesAndApprovedApprovals($studentIds);
        $students = ArrayToolkit::index($students, 'userId');

        $usersLearnedTime = $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeByCourseIdGroupByUserId($courseId, $studentIds);
        $usersPureLearnedTime = $this->getCoursePlanLearnDataDailyStatisticsService()->sumPureLearnedTimeByCourseIdGroupByUserId($courseId, $studentIds);

        foreach ($students as $key => &$student) {
            $user = $this->getUserService()->getUser($student['userId']);
            $student['nickname'] = $user['nickname'];

            $questionCount = $this->getThreadService()->countThreads(
                ['courseId' => $courseId, 'type' => 'question', 'userId' => $user['id']]
            );
            $student['questionCount'] = $questionCount;

            if ($student['finishedTime'] > 0) {
                $student['fininshDay'] = intval(($student['finishedTime'] - $student['createdTime']) / (60 * 60 * 24));
            } else {
                $student['fininshDay'] = intval((time() - $student['createdTime']) / (60 * 60 * 24));
            }

            $student['mobile'] = isset($userProfilesAndApprovedApprovals['usersProfile'][$key]['mobile']) ? $userProfilesAndApprovedApprovals['usersProfile'][$key]['mobile'] : '';
            $student['idcard'] = isset($userProfilesAndApprovedApprovals['usersApproval'][$key]['idcard']) ? $userProfilesAndApprovedApprovals['usersApproval'][$key]['idcard'] : '';

            $student['learnTime'] = empty($usersLearnedTime[$student['userId']]) ? 0 : $usersLearnedTime[$student['userId']]['learnedTime'];
            $student['pureLearnTime'] = empty($usersPureLearnedTime[$student['userId']]) ? 0 : $usersPureLearnedTime[$student['userId']]['learnedTime'];
            $student['fininshDay'] <= 0 && $student['fininshDay'] = 0;
        }

        $coursePlanSumLearnedTime = $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeByCourseId($courseId);

        return $this->render(
            'admin-v2/teach/course-set/course-data-modal.html.twig',
            [
                'courseSet' => $courseSet,
                'courses' => $courses,
                'paginator' => $paginator,
                'students' => $students,
                'coursePlanSumLearnedTime' => empty($coursePlanSumLearnedTime) ? 0 : round($coursePlanSumLearnedTime / 60, 1),
                'courseId' => $courseId,
            ]
        );
    }

    public function cloneAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        return $this->render(
            'admin-v2/teach/course-set/course-set-clone-modal.html.twig',
            [
                'courseSet' => $courseSet,
            ]
        );
    }

    public function cloneByCrontabAction(Request $request, $courseSetId)
    {
        $jobName = 'clone_course_set_' . $courseSetId;
        $jobs = $this->getSchedulerService()->countJobs(['name' => $jobName, 'deleted' => 0]);
        $title = $request->request->get('title');
        $user = $this->getCurrentUser();

        if ($jobs) {
            return new JsonResponse(['success' => 0, 'msg' => 'notify.job_redo_warning.hint']);
        } else {
            //复制整个课程，在预期时间后一个小时有效，非无限时间
            $this->getSchedulerService()->register([
                'name' => $jobName,
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => intval(time() + 10),
                'class' => 'Biz\Course\Job\CloneCourseSetJob',
                'args' => ['courseSetId' => $courseSetId, 'userId' => $user->getId(), 'params' => ['title' => $title]],
                'misfire_threshold' => 60 * 60,
            ]);
        }

        return new JsonResponse(['success' => 1, 'msg' => 'notify.course_set_clone_start.message']);
    }

    public function cloneByWebAction(Request $request, $courseSetId)
    {
        $title = $request->request->get('title');
        $this->getCourseSetService()->cloneCourseSet($courseSetId, ['title' => $title]);

        return new JsonResponse(['success' => 1]);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function renderCourseTr($courseId, $request)
    {
        $fields = $request->query->all();
        $courseSet = $this->getCourseSetService()->getCourseSet($courseId);
        $courseSet['defaultCourse'] = $this->getCourseService()->getDefaultCourseByCourseSetId($courseId);
        $default = $this->getSettingService()->get('default', []);
        $classrooms = [];
        $vips = [];

        if ('classroom' == $fields['filter']) {
            $classrooms = $this->getClassroomService()->findClassroomCourseByCourseSetIds([$courseSet['id']]);
            $classrooms = ArrayToolkit::index($classrooms, 'courseSetId');

            foreach ($classrooms as $key => $classroom) {
                $classroomInfo = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        } elseif ('vip' == $fields['filter']) {
            if ($this->isPluginInstalled('Vip')) {
                $vips = $this->getVipLevelService()->searchLevels([], 0, PHP_INT_MAX);
                $vips = ArrayToolkit::index($vips, 'id');
            }
        }

        return $this->render(
            'admin-v2/teach/course-set/tr.html.twig',
            [
                'user' => $this->getUserService()->getUser($courseSet['creator']),
                'category' => isset($courseSet['categoryId']) ? $this->getCategoryService()->getCategory(
                    $courseSet['categoryId']
                ) : [],
                'courseSet' => $courseSet,
                'default' => $default,
                'classrooms' => $classrooms,
                'filter' => $fields['filter'],
                'vips' => $vips,
            ]
        );
    }

    protected function statsCourseSetData($courseSets)
    {
        if (empty($courseSets)) {
            return $courseSets;
        }

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courseSetIncomes = $this->getCourseSetService()->findCourseSetIncomesByCourseSetIds($courseSetIds);
        $courseSetIncomes = ArrayToolkit::index($courseSetIncomes, 'courseSetId');

        $defaultCourseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
        $defaultCourses = $this->getCourseService()->findCoursesByIds($defaultCourseIds);

        $tasks = $this->getTaskService()->findTasksByCourseSetIds($courseSetIds);
        $tasks = ArrayToolkit::group($tasks, 'fromCourseSetId');

        foreach ($courseSets as &$courseSet) {
            $courseSetId = $courseSet['id'];
            $defaultCourseId = $courseSet['defaultCourseId'];
            $courses = $this->getCourseService()->searchCourses(['courseSetId' => $courseSetId], [], 0, PHP_INT_MAX, ['id', 'compulsoryTaskNum']);

            $isLearnedNum = 0;
            foreach ($courses as $course) {
                $isLearnedNum = $isLearnedNum + $this->getMemberService()->countMembers([
                        'finishedTime_GT' => 0,
                        'courseId' => $course['id'],
                        'role' => 'student',
                        'learnedCompulsoryTaskNumGreaterThan' => $course['compulsoryTaskNum'],
                    ]);
            }

            if (!empty($courseSetIncomes[$courseSetId])) {
                $courseSet['income'] = $courseSetIncomes[$courseSetId]['income'];
            } else {
                $courseSet['income'] = 0;
            }
            $courseSet['isLearnedNum'] = $isLearnedNum;
            $courseSet['taskCount'] = $courseSet['compulsorTaskCount'] = $courseSet['electiveTaskNum'] = 0;
            if (!empty($tasks[$courseSetId])) {
                $courseSet['taskCount'] = count($tasks[$courseSetId]);
                $courseSetTasks = ArrayToolkit::group($tasks[$courseSetId], 'isOptional');
                $courseSet['compulsorTaskCount'] = empty($courseSetTasks['0']) ? 0 : count($courseSetTasks['0']);
                $courseSet['electiveTaskNum'] = $courseSet['taskCount'] - $courseSet['compulsorTaskCount'];
            }
            $courseSet['courseCount'] = count($courses);
            $courseSet['studentNum'] = $this->getMemberService()->countStudentMemberByCourseSetId($courseSetId);
        }

        return $courseSets;
    }

    //@deprecated
    protected function returnDeleteStatus($result, $type)
    {
        $dataDictionary = [
            'questions' => '问题',
            'testpapers' => '试卷',
            'materials' => '课时资料',
            'chapters' => '课时章节',
            'drafts' => '课时草稿',
            'lessons' => '课时',
            'lessonLearns' => '课时时长',
            'lessonReplays' => '课时录播',
            'lessonViews' => '课时播放时长',
            'homeworks' => '课时作业',
            'exercises' => '课时练习',
            'favorites' => '课时收藏',
            'notes' => '课时笔记',
            'threads' => '课程话题',
            'reviews' => '课程评价',
            'announcements' => '课程公告',
            'statuses' => '课程动态',
            'members' => '课程成员',
            'conversation' => '会话',
            'course' => '课程',
        ];

        if ($result > 0) {
            $message = $dataDictionary[$type] . '数据删除';

            return ['success' => true, 'message' => $message];
        } else {
            if ('homeworks' == $type || 'exercises' == $type) {
                $message = $dataDictionary[$type] . '数据删除失败或插件未安装或插件未升级';

                return ['success' => false, 'message' => $message];
            } elseif ('course' == $type) {
                $message = $dataDictionary[$type] . '数据删除';

                return ['success' => false, 'message' => $message];
            } else {
                $message = $dataDictionary[$type] . '数据删除失败';

                return ['success' => false, 'message' => $message];
            }
        }
    }

    public function chooserAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['parentId'] = 0;

        if (isset($conditions['categoryId']) && '' == $conditions['categoryId']) {
            unset($conditions['categoryId']);
        }

        if (isset($conditions['status']) && '' == $conditions['status']) {
            unset($conditions['status']);
        }

        if (isset($conditions['title']) && '' == $conditions['title']) {
            unset($conditions['title']);
        }

        if (isset($conditions['creatorName']) && '' == $conditions['creatorName']) {
            unset($conditions['creatorName']);
        }

        $withCoursePlan = false;
        if (!empty($conditions['withPlan'])) {
            $withCoursePlan = true;
            unset($conditions['withPlan']);
        }

        $count = $this->getCourseSetService()->countCourseSets($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        if ($withCoursePlan) {
            $courseSets = $this->searchCourseSetWithCourses(
                $conditions,
                null,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        } else {
            $courseSets = $this->getCourseSetService()->searchCourseSets(
                $conditions,
                null,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        return $this->render(
            'admin-v2/teach/course/course-set-chooser.html.twig',
            [
                'users' => $users,
                'conditions' => $conditions,
                'courseSets' => $courseSets,
                'categories' => $categories,
                'paginator' => $paginator,
                'withCoursePlan' => $withCoursePlan,
            ]
        );
    }

    private function searchCourseSetWithCourses($conditions, $orderbys, $start, $limit)
    {
        $conditions['status'] = 'published'; //计划模式下，只取发布的课程和计划
        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, $orderbys, $start, $limit);

        if (empty($courseSets)) {
            return [];
        }

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array_keys($courseSets));
        if (!empty($courses)) {
            foreach ($courses as $course) {
                if ('published' != $course['status']) {
                    continue;
                }
                if (empty($courseSets[$course['courseSetId']]['courses'])) {
                    $courseSets[$course['courseSetId']]['courses'] = [$course];
                } else {
                    $courseSets[$course['courseSetId']]['courses'][] = $course;
                }
            }
        }

        return array_values($courseSets);
    }

    public function courseListAction(Request $request, $id)
    {
        $conditions = [
            'courseSetId' => $id,
        ];

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->countCourses($conditions),
            10
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            ['seq' => 'DESC', 'createdTime' => 'asc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($courses, 'creator');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin-v2/teach/course-set/course-list-modal.html.twig', [
            'courses' => $courses,
            'users' => $users,
            'paginator' => $paginator,
        ]);
    }

    public function courseTagMatchAction(Request $request)
    {
        $queryString = $request->query->get('q');

        $tags = $this->getTagService()->searchTags(['likeName' => $queryString], [], 0, PHP_INT_MAX);

        return $this->createJsonResponse($tags);
    }

    public function prepareForExportCourseDetailDataAction(Request $request, $courseId)
    {
        if (empty($courseId)) {
            return $this->createJsonResponse(['error' => 'courseId can not be null']);
        }

        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        list($title, $members, $courseMemberCount) = $this->getExportCourseMemberData(
            $courseId,
            $start,
            $limit,
            $exportAllowCount
        );

        $file = '';
        if (0 == $start) {
            $file = ExportHelp::addFileTitle($request, 'course_detail', $title);
        }

        $datas = implode("\r\n", $members);
        $fileName = ExportHelp::saveToTempFile($request, $datas, $file);

        $method = ExportHelp::getNextMethod($start + $limit, $courseMemberCount);

        return $this->createJsonResponse(
            [
                'method' => $method,
                'fileName' => $fileName,
                'start' => $start + $limit,
            ]
        );
    }

    public function exportCourseDetailDataAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        if (empty($course)) {
            return $this->createJsonResponse(['error' => 'course can not be found']);
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $courseTitle = 1 == $course['isDefault'] ? $courseSet['title'] : $courseSet['title'] . '-' . $course['title'];
        $fileName = sprintf('%s-(%s).csv', $courseTitle, date('Y-n-d'));

        return ExportHelp::exportCsv($request, $fileName);
    }

    protected function getExportCourseMemberData($courseId, $start, $limit, $exportAllowCount)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $count = $this->getMemberService()->countMembers(['courseId' => $courseId, 'role' => 'student']);
        $count = ($count > $exportAllowCount) ? $exportAllowCount : $count;

        $students = $this->getMemberService()->searchMembers(
            ['courseId' => $courseId, 'role' => 'student'],
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );
        $studentIds = ArrayToolkit::column($students, 'userId');

        $usersLearnedTime = $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeByCourseIdGroupByUserId($courseId, $studentIds);

        $userProfilesAndApprovedApprovals = $this->makeUserProfilesAndApprovedApprovals($studentIds);

        $students = ArrayToolkit::index($students, 'userId');

        $exportMembers = [];
        foreach ($students as $key => $student) {
            $exportMember = [];
            $user = $this->getUserService()->getUser($student['userId']);
            $exportMember['nickname'] = is_numeric($user['nickname']) ? $user['nickname'] . "\t" : $user['nickname'];
            $exportMember['mobile'] = empty($userProfilesAndApprovedApprovals['usersProfile'][$key]['mobile']) ? '--' : $userProfilesAndApprovedApprovals['usersProfile'][$key]['mobile'] . "\t";
            $exportMember['idcard'] = empty($userProfilesAndApprovedApprovals['usersApproval'][$key]['idcard']) ? '--' : $userProfilesAndApprovedApprovals['usersApproval'][$key]['idcard'] . "\t";
            $exportMember['joinTime'] = date('Y-m-d H:i:s', $student['createdTime']);

            if ($student['finishedTime'] > 0) {
                $exportMember['finishedTime'] = date('Y-m-d H:i:s', $student['finishedTime']);
                $exportMember['studyDays'] = intval(($student['finishedTime'] - $student['createdTime']) / (60 * 60 * 24));
            } else {
                $exportMember['finishedTime'] = '--';
                $exportMember['studyDays'] = intval((time() - $student['createdTime']) / (60 * 60 * 24));
            }

            $learnTime = empty($usersLearnedTime[$student['userId']]) ? 0 : $usersLearnedTime[$student['userId']]['learnedTime'];
            $exportMember['learnTime'] = $learnTime > 0 ? round($learnTime / 60, 1) : '--';

            $questionCount = $this->getThreadService()->countThreads(
                ['courseId' => $courseId, 'type' => 'question', 'userId' => $user['id']]
            );
            $exportMember['questionNum'] = $questionCount;

            $exportMember['noteNum'] = $student['noteNum'];

            $exportMembers[] = implode(',', $exportMember);
        }

        $titles = [
            $this->trans('admin.course_manage.statistics.data_detail.name'),
            $this->trans('admin.course_manage.statistics.data_detail.phone_number'),
            $this->trans('admin.course_manage.statistics.data_detail.id_number'),
            $this->trans('admin.course_manage.statistics.data_detail.join_time'),
            $this->trans('admin.course_manage.statistics.data_detail.finished_time'),
            $this->trans('admin.course_manage.statistics.data_detail.study_days'),
            $this->trans('admin.course_manage.statistics.data_detail.study_time'),
            $this->trans('admin.course_manage.statistics.data_detail.question_number'),
            $this->trans('admin.course_manage.statistics.data_detail.note_number'),
        ];

        return [implode(',', $titles), $exportMembers, $count];
    }

    protected function getDifferentCourseSetsNum($conditions)
    {
        $total = $this->getCourseSetService()->countCourseSets($conditions);
        $published = $this->getCourseSetService()->countCourseSets(array_merge($conditions, ['status' => 'published']));
        $closed = $this->getCourseSetService()->countCourseSets(array_merge($conditions, ['status' => 'closed']));
        $draft = $this->getCourseSetService()->countCourseSets(array_merge($conditions, ['status' => 'draft']));

        return [
            'total' => empty($total) ? 0 : $total,
            'published' => empty($published) ? 0 : $published,
            'closed' => empty($closed) ? 0 : $closed,
            'draft' => empty($draft) ? 0 : $draft,
        ];
    }

    protected function filterCourseSetConditions($filter, $conditions)
    {
        switch ($filter) {
            case 'all':
                break;
            case 'classroom':
                $conditions['isClassroomRef'] = 1;
                break;
            case 'vip':
                $conditions = $this->getVipCourseSetConditions($conditions);
                $conditions['parentId'] = 0;
                $conditions['isClassroomRef'] = 0;
                break;
            default:
                $conditions['parentId'] = 0;
                $conditions['isClassroomRef'] = 0;
                $conditions = $this->filterCourseSetType($conditions);
        }

        $conditions = $this->fillOrgCode($conditions);

        if (!empty($conditions['categoryId'])) {
            $categorIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $categorIds[] = $conditions['categoryId'];
            $conditions['categoryIds'] = $categorIds;
            unset($conditions['categoryId']);
        }

        if (!empty($conditions['tagId'])) {
            $conditions['tagIds'] = [$conditions['tagId']];
            $conditions = $this->getCourseConditionsByTags($conditions);
        }

        return $conditions;
    }

    protected function getVipCourseSetConditions($conditions)
    {
        $vipRights = $this->getVipRightService()->findVipRightsBySupplierCode('course');
        $courseIds = ArrayToolkit::column($vipRights, 'uniqueCode');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $conditions['ids'] = ArrayToolkit::column($courses, 'courseSetId');

        return $conditions;
    }

    protected function findRelatedOptions($filter, $courseSets)
    {
        $classroomCourses = [];
        $coursesCount = [];

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        if ('classroom' == $filter) {
            $classroomCourses = $this->getClassroomService()->findClassroomCourseByCourseSetIds($courseSetIds);

            $classroomIds = ArrayToolkit::column($classroomCourses, 'classroomId');
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
            $classrooms = ArrayToolkit::index($classrooms, 'id');

            array_walk($classroomCourses, function (&$course, $key) use ($classrooms) {
                $course['classroomTitle'] = empty($classrooms[$course['classroomId']]) ? '' : $classrooms[$course['classroomId']]['title'];
            });
            $classroomCourses = ArrayToolkit::index($classroomCourses, 'courseSetId');
        } elseif ('vip' == $filter) {
            $courseSets = $this->_fillVipCourseSetLevels($courseSets);
        } else {
            $coursesCount = $this->getCourseService()->countCoursesGroupByCourseSetIds($courseSetIds);
            $coursesCount = ArrayToolkit::index($coursesCount, 'courseSetId');
        }

        return [$courseSets, $coursesCount, $classroomCourses];
    }

    private function _fillVipCourseSetLevels($courseSets)
    {
        foreach ($courseSets as &$courseSet) {
            $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
            $vipRights = $this->getVipRightService()->findVipRightBySupplierCodeAndUniqueCodes(CourseVipRightSupplier::CODE, array_column($courses, 'id'));
            $levelIds = array_column($vipRights, 'vipLevelId');
            $levels = $this->getVipLevelService()->searchLevels(
                ['ids' => $levelIds],
                ['seq' => 'ASC'],
                0,
                PHP_INT_MAX
            );
            $courseSet['levels'] = $levels;
        }

        return $courseSets;
    }

    private function makeUserProfilesAndApprovedApprovals($userIds)
    {
        $usersProfile = $this->getUserService()->findUserProfilesByIds($userIds);
        $usersApproval = $this->getUserService()->searchApprovals(
            ['userIds' => $userIds, 'status' => 'approved'], [], 0, count($userIds)
        );
        $usersProfile = ArrayToolkit::index($usersProfile, 'id');
        $usersApproval = ArrayToolkit::index($usersApproval, 'userId');

        return [
            'usersProfile' => $usersProfile,
            'usersApproval' => $usersApproval,
        ];
    }

    protected function filterCourseSetType($conditions)
    {
        if (!$this->getWebExtension()->isPluginInstalled('Reservation')) {
            $conditions['excludeTypes'] = ['reservation'];
        }

        return $conditions;
    }

    protected function getCourseConditionsByTags($conditions)
    {
        if (empty($conditions['tagIds'])) {
            return $conditions;
        }

        $tagOwnerIds = $this->getTagService()->findOwnerIdsByTagIdsAndOwnerType($conditions['tagIds'], 'course-set');

        $conditions['ids'] = empty($tagOwnerIds) ? [-1] : $tagOwnerIds;
        unset($conditions['tagIds']);

        return $conditions;
    }

    protected function buildCourseSetTags($courseSets)
    {
        $tags = [];
        foreach ($courseSets as $courseSet) {
            $tags = array_merge($tags, $courseSet['tags']);
        }

        $tags = $this->getTagService()->findTagsByIds($tags);
        $allTagIds = ArrayToolkit::column($tags, 'id');

        foreach ($courseSets as &$courseSet) {
            $courseSet['tags'] = array_values(array_intersect($courseSet['tags'], $allTagIds));

            if (!empty($courseSet['tags'])) {
                $courseSet['displayTag'] = $tags[$courseSet['tags'][0]]['name'];
                if (count($courseSet['tags']) > 1) {
                    $courseSet['displayTagNames'] = $this->buildTagsDisplayNames($courseSet['tags'], $tags);
                }
            }
        }

        return $courseSets;
    }

    protected function buildTagsDisplayNames(array $tagIds, array $tags, $delimiter = '/')
    {
        $tagsNames = '';

        foreach ($tagIds as $tagId) {
            if (!empty($tags[$tagId])) {
                $tagsNames = $tagsNames . $delimiter . $tags[$tagId]['name'];
            }
        }

        return trim($tagsNames, $delimiter);
    }

    protected function removeUnpublishAndNonDefaultCourses($courses)
    {
        foreach ($courses as $key => $course) {
            if ('published' != $course['status'] && 1 != $course['isDefault']) {
                unset($courses[$key]);
            }
        }

        return $courses;
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
     * @return CourseDeleteService
     */
    protected function getCourseSetDeleteService()
    {
        return $this->createService('Course:CourseSetDeleteService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return MessageDigestPasswordEncoder
     */
    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    /**
     * @return LevelService
     */
    protected function getVipLevelService()
    {
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    /**
     * @return VipRightService
     */
    protected function getVipRightService()
    {
        return $this->createService('VipPlugin:Marketing:VipRightService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return TagServiceImpl
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getWebExtension()
    {
        return $this->get('web.twig.extension');
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }

    /**
     * @return SyncEventService
     */
    protected function getSyncEventService()
    {
        return $this->createService('S2B2C:SyncEventService');
    }

    /**
     * @return ActivityLearnDataService
     */
    protected function getActivityLearnDataService()
    {
        return $this->createService('Visualization:ActivityLearnDataService');
    }

    /**
     * @return CoursePlanLearnDataDailyStatisticsService
     */
    protected function getCoursePlanLearnDataDailyStatisticsService()
    {
        return $this->createService('Visualization:CoursePlanLearnDataDailyStatisticsService');
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    private function getProductMallGoodsRelationService()
    {
        return $this->createService('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
