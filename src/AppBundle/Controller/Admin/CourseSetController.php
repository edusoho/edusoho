<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\Task\Service\TaskService;
use Biz\User\UserException;
use Biz\Taxonomy\Service\Impl\TagServiceImpl;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Course\Service\CourseSetService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseDeleteService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\ActivityLearnLogService;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use VipPlugin\Biz\Vip\Service\LevelService;

class CourseSetController extends BaseController
{
    public function indexAction(Request $request, $filter)
    {
        $conditions = $request->query->all();
        $conditions['excludeTypes'] = array('reservation');
        $conditions = $this->filterCourseSetConditions($filter, $conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($courseSets, $coursesCount, $classroomCourses) = $this->findRelatedOptions($filter, $courseSets);

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));
        $courseSetStatusNum = $this->getDifferentCourseSetsNum($conditions);
        $courseSets = $this->buildCourseSetTags($courseSets);

        return $this->render(
            'admin/course-set/index.html.twig',
            array(
                'courseSets' => $courseSets,
                'users' => $users,
                'categories' => $categories,
                'paginator' => $paginator,
                'classrooms' => $classroomCourses,
                'filter' => $filter,
                'tag' => empty($conditions['tagId']) ? array() : $this->getTagService()->getTag($conditions['tagId']),
                'courseSetStatusNum' => $courseSetStatusNum,
                'coursesCount' => $coursesCount,
            )
        );
    }

    protected function getDifferentCourseSetsNum($conditions)
    {
        $total = $this->getCourseSetService()->countCourseSets($conditions);
        $published = $this->getCourseSetService()->countCourseSets(array_merge($conditions, array('status' => 'published')));
        $closed = $this->getCourseSetService()->countCourseSets(array_merge($conditions, array('status' => 'closed')));
        $draft = $this->getCourseSetService()->countCourseSets(array_merge($conditions, array('status' => 'draft')));

        return array(
            'total' => empty($total) ? 0 : $total,
            'published' => empty($published) ? 0 : $published,
            'closed' => empty($closed) ? 0 : $closed,
            'draft' => empty($draft) ? 0 : $draft,
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

        if (!$currentUser->hasPermission('admin_course_set_delete')) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        $classroomRef = $this->getClassroomService()->getClassroomCourseByCourseSetId($id);
        if (!empty($classroomRef)) {
            return $this->createJsonResponse(array('code' => 2, 'message' => '请先从班级管理将本课程移除'));
        }
        $subCourses = $this->getCourseSetService()->findCourseSetsByParentIdAndLocked($id, 1);
        if (!empty($subCourses) || ($courseSet['parentId'] && 1 == $courseSet['locked'])) {
            return $this->createJsonResponse(array('code' => 2, 'message' => '请先删除班级课程'));
        }
        if ('draft' == $courseSet['status']) {
            $this->getCourseSetService()->deleteCourseSet($id);

            return $this->createJsonResponse(array('code' => 0, 'message' => '删除课程成功'));
        }

        $isCheckPassword = $request->getSession()->get('checkPassword');
        if (!$isCheckPassword) {
            return $this->render('admin/course/delete.html.twig', array('courseSet' => $courseSet));
        }

        $request->getSession()->remove('checkPassword');

        $this->getCourseSetService()->deleteCourseSet($id);

        return $this->createJsonResponse(array('code' => 0, 'message' => '删除课程成功'));
    }

    public function checkPasswordAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $password = $request->request->get('password');
            $currentUser = $this->getUser();
            $password = $this->getPasswordEncoder()->encodePassword($password, $currentUser->salt);

            if ($password == $currentUser->password) {
                $response = array('success' => true, 'message' => '密码正确');
                $request->getSession()->set('checkPassword', true);
            } else {
                $response = array('success' => false, 'message' => '密码错误');
            }

            return $this->createJsonResponse($response);
        }
        $this->createNewException(CommonException::NOT_ALLOWED_METHOD());
    }

    public function publishAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        if ('live' == $courseSet['type']) {
            $course = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSet['id']);

            if (empty($course['maxStudentNum'])) {
                return $this->createJsonResponse(array(
                    'success' => false,
                    'message' => '直播课程发布前需要在计划设置中设置课程人数',
                ));
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
                    'admin/course-set/course-recommend-tr.html.twig',
                    array(
                        'courseSet' => $courseSet,
                        'user' => $user,
                    )
                );
            }

            return $this->renderCourseTr($id, $request);
        }

        return $this->render(
            'admin/course-set/course-recommend-modal.html.twig',
            array(
                'courseSet' => $courseSet,
                'ref' => $ref,
                'filter' => $filter,
            )
        );
    }

    public function cancelRecommendAction(Request $request, $id, $target)
    {
        $this->getCourseSetService()->cancelRecommendCourse($id);

        if ('recommend_list' == $target) {
            return $this->createJsonResponse(array('success' => 1));
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
            'admin/course-set/course-recommend-list.html.twig',
            array(
                'courseSets' => $courseSets,
                'users' => $users,
                'paginator' => $paginator,
                'categories' => $categories,
            )
        );
    }

    public function dataAction(Request $request, $filter)
    {
        $conditions = $request->query->all();

        if ('normal' == $filter) {
            $conditions['parentId'] = 0;
            $conditions['excludeTypes'] = array('reservation');
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
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $classrooms = array();

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

        $courseSetIncomes = $this->getCourseSetService()->findCourseSetIncomesByCourseSetIds($courseSetIds);
        $courseSetIncomes = ArrayToolkit::index($courseSetIncomes, 'courseSetId');

        $courseIds = ArrayToolkit::column($courseSets, 'defaultCourseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        foreach ($courseSets as $key => &$courseSet) {
            // TODO 完成人数目前只统计了默认教学计划
            $courseSetId = $courseSet['id'];
            $defaultCourseId = $courseSet['defaultCourseId'];
            $courseCount = $this->getCourseService()->searchCourseCount(array('courseSetId' => $courseSetId));
            $isLearnedNum = empty($courses[$defaultCourseId]) ? 0 : $this->getMemberService()->countMembers(
                array('finishedTime_GT' => 0, 'courseId' => $courseSet['defaultCourseId'], 'learnedCompulsoryTaskNumGreaterThan' => $courses[$defaultCourseId]['compulsoryTaskNum'])
            );

            $taskCount = $this->getTaskService()->countTasks(array('fromCourseSetId' => $courseSetId));

            $courseSet['learnedTime'] = $this->getTaskService()->sumCourseSetLearnedTimeByCourseSetId($courseSetId);
            $courseSet['learnedTime'] = round($courseSet['learnedTime'] / 60);
            if (!empty($courseSetIncomes[$courseSetId])) {
                $courseSet['income'] = $courseSetIncomes[$courseSetId]['income'];
            } else {
                $courseSet['income'] = 0;
            }
            $courseSet['isLearnedNum'] = $isLearnedNum;
            $courseSet['taskCount'] = $taskCount;
            $courseSet['courseCount'] = $courseCount;
            $courseSet['studentNum'] = $this->getMemberService()->countStudentMemberByCourseSetId($courseSetId);
        }

        return $this->render(
            'admin/course-set/data.html.twig',
            array(
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'filter' => $filter,
                'classrooms' => $classrooms,
            )
        );
    }

    public function detailDataAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($id);
        $courseId = $request->query->get('courseId');

        if (empty($courseId)) {
            $courseId = $courses[0]['id'];
        }

        $count = $this->getMemberService()->countMembers(array('courseId' => $courseId, 'role' => 'student'));

        $paginator = new Paginator($this->get('request'), $count, 20);

        $students = $this->getMemberService()->searchMembers(
                array('courseId' => $courseId, 'role' => 'student'),
                array('createdTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

        foreach ($students as $key => &$student) {
            $user = $this->getUserService()->getUser($student['userId']);
            $student['nickname'] = $user['nickname'];

            $questionCount = $this->getThreadService()->countThreads(
                array('courseId' => $courseId, 'type' => 'question', 'userId' => $user['id'])
            );
            $student['questionCount'] = $questionCount;

            if ($student['finishedTime'] > 0) {
                $student['fininshDay'] = intval(($student['finishedTime'] - $student['createdTime']) / (60 * 60 * 24));
            } else {
                $student['fininshDay'] = intval((time() - $student['createdTime']) / (60 * 60 * 24));
            }

            $student['learnTime'] = intval($student['lastLearnTime'] - $student['createdTime']);
        }

        return $this->render(
            'admin/course-set/course-data-modal.html.twig',
            array(
                'courseSet' => $courseSet,
                'courses' => $courses,
                'paginator' => $paginator,
                'students' => $students,
                'courseId' => $courseId,
            )
        );
    }

    public function cloneAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        return $this->render(
            'admin/course-set/course-set-clone-modal.html.twig',
            array(
                'courseSet' => $courseSet,
            )
        );
    }

    public function cloneByCrontabAction(Request $request, $courseSetId)
    {
        $jobName = 'clone_course_set_'.$courseSetId;
        $jobs = $this->getSchedulerService()->countJobs(array('name' => $jobName, 'deleted' => 0));
        $title = $request->request->get('title');
        $user = $this->getCurrentUser();

        if ($jobs) {
            return new JsonResponse(array('success' => 0, 'msg' => 'notify.job_redo_warning.hint'));
        } else {
            //复制整个课程，在预期时间后一个小时有效，非无限时间
            $this->getSchedulerService()->register(array(
                'name' => $jobName,
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => intval(time() + 10),
                'class' => 'Biz\Course\Job\CloneCourseSetJob',
                'args' => array('courseSetId' => $courseSetId, 'userId' => $user->getId(), 'params' => array('title' => $title)),
                'misfire_threshold' => 60 * 60,
            ));
        }

        return new JsonResponse(array('success' => 1, 'msg' => 'notify.course_set_clone_start.message'));
    }

    public function cloneByWebAction(Request $request, $courseSetId)
    {
        $title = $request->request->get('title');
        $this->getCourseSetService()->cloneCourseSet($courseSetId, array('title' => $title));

        return new JsonResponse(array('success' => 1));
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
        $default = $this->getSettingService()->get('default', array());
        $classrooms = array();
        $vips = array();

        if ('classroom' == $fields['filter']) {
            $classrooms = $this->getClassroomService()->findClassroomCourseByCourseSetIds(array($courseSet['id']));
            $classrooms = ArrayToolkit::index($classrooms, 'courseSetId');

            foreach ($classrooms as $key => $classroom) {
                $classroomInfo = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        } elseif ('vip' == $fields['filter']) {
            if ($this->isPluginInstalled('Vip')) {
                $vips = $this->getVipLevelService()->searchLevels(array(), 0, PHP_INT_MAX);
                $vips = ArrayToolkit::index($vips, 'id');
            }
        }

        return $this->render(
            'admin/course-set/tr.html.twig',
            array(
                'user' => $this->getUserService()->getUser($courseSet['creator']),
                'category' => isset($courseSet['categoryId']) ? $this->getCategoryService()->getCategory(
                    $courseSet['categoryId']
                ) : array(),
                'courseSet' => $courseSet,
                'default' => $default,
                'classrooms' => $classrooms,
                'filter' => $fields['filter'],
                'vips' => $vips,
            )
        );
    }

    //@deprecated
    protected function returnDeleteStatus($result, $type)
    {
        $dataDictionary = array(
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
        );

        if ($result > 0) {
            $message = $dataDictionary[$type].'数据删除';

            return array('success' => true, 'message' => $message);
        } else {
            if ('homeworks' == $type || 'exercises' == $type) {
                $message = $dataDictionary[$type].'数据删除失败或插件未安装或插件未升级';

                return array('success' => false, 'message' => $message);
            } elseif ('course' == $type) {
                $message = $dataDictionary[$type].'数据删除';

                return array('success' => false, 'message' => $message);
            } else {
                $message = $dataDictionary[$type].'数据删除失败';

                return array('success' => false, 'message' => $message);
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
            'admin/course/course-set-chooser.html.twig',
            array(
                'users' => $users,
                'conditions' => $conditions,
                'courseSets' => $courseSets,
                'categories' => $categories,
                'paginator' => $paginator,
                'withCoursePlan' => $withCoursePlan,
            )
        );
    }

    private function searchCourseSetWithCourses($conditions, $orderbys, $start, $limit)
    {
        $conditions['status'] = 'published'; //计划模式下，只取发布的课程和计划
        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, $orderbys, $start, $limit);

        if (empty($courseSets)) {
            return array();
        }

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array_keys($courseSets));
        if (!empty($courses)) {
            foreach ($courses as $course) {
                if ('published' != $course['status']) {
                    continue;
                }
                if (empty($courseSets[$course['courseSetId']]['courses'])) {
                    $courseSets[$course['courseSetId']]['courses'] = array($course);
                } else {
                    $courseSets[$course['courseSetId']]['courses'][] = $course;
                }
            }
        }

        return array_values($courseSets);
    }

    public function courseListAction(Request $request, $id)
    {
        $conditions = array(
            'courseSetId' => $id,
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->countCourses($conditions),
            10
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            array('seq' => 'DESC', 'createdTime' => 'asc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($courses, 'creator');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/course-set/course-list-modal.html.twig', array(
            'courses' => $courses,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function courseTagMatchAction(Request $request)
    {
        $queryString = $request->query->get('q');

        $tags = $this->getTagService()->searchTags(array('likeName' => $queryString), array(), 0, PHP_INT_MAX);

        return $this->createJsonResponse($tags);
    }

    protected function filterCourseSetConditions($filter, $conditions)
    {
        if ('classroom' == $filter) {
            $conditions['parentId_GT'] = 0;
        } elseif ('vip' == $filter) {
            $conditions['isVip'] = 1;
            $conditions['parentId'] = 0;
        } else {
            $conditions['parentId'] = 0;
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
            $conditions['tagIds'] = array($conditions['tagId']);
            $conditions = $this->getCourseConditionsByTags($conditions);
        }

        return $conditions;
    }

    protected function findRelatedOptions($filter, $courseSets)
    {
        $classroomCourses = array();
        $coursesCount = array();

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

        return array($courseSets, $coursesCount, $classroomCourses);
    }

    private function _fillVipCourseSetLevels($courseSets)
    {
        foreach ($courseSets as &$courseSet) {
            $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
            $levelIds = ArrayToolkit::column($courses, 'vipLevelId');
            $levelIds = array_unique($levelIds);
            $levels = $this->getVipLevelService()->searchLevels(
                array('ids' => $levelIds),
                array('seq' => 'ASC'),
                0,
                PHP_INT_MAX
            );
            $courseSet['levels'] = $levels;
        }

        return $courseSets;
    }

    protected function filterCourseSetType($conditions)
    {
        if (!$this->getWebExtension()->isPluginInstalled('Reservation')) {
            $conditions['excludeTypes'] = array('reservation');
        }

        return $conditions;
    }

    protected function getCourseConditionsByTags($conditions)
    {
        if (empty($conditions['tagIds'])) {
            return $conditions;
        }

        $tagOwnerIds = $this->getTagService()->findOwnerIdsByTagIdsAndOwnerType($conditions['tagIds'], 'course-set');

        $conditions['ids'] = empty($tagOwnerIds) ? array(-1) : $tagOwnerIds;
        unset($conditions['tagIds']);

        return $conditions;
    }

    protected function buildCourseSetTags($courseSets)
    {
        $tags = array();
        foreach ($courseSets as $courseSet) {
            $tags = array_merge($tags, $courseSet['tags']);
        }
        $tags = $this->getTagService()->findTagsByIds($tags);
        foreach ($courseSets as &$courseSet) {
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
                $tagsNames = $tagsNames.$delimiter.$tags[$tagId]['name'];
            }
        }

        return trim($tagsNames, $delimiter);
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
}
