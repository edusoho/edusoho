<?php
namespace AppBundle\Controller\Admin;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class CourseSetController extends BaseController
{
    public function indexAction(Request $request, $filter)
    {
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

        $conditions = $this->fillOrgCode($conditions);

        $count = $this->getCourseSetService()->countCourseSets($conditions);

        $paginator  = new Paginator($this->get('request'), $count, 20);
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $courseSetIds   = ArrayToolkit::column($courseSets, 'id');
        $defaultCourses = $this->getCourseService()->getDefaultCoursesByCourseSetIds($courseSetIds);
        $defaultCourses = ArrayToolkit::index($defaultCourses, 'courseSetId');

        foreach ($courseSets as &$courseSet) {
            $courseSet['defaultCourse'] = $defaultCourses[$courseSet['id']];
        }

        list($searchCourseSetsNum, $publishedCourseSetsNum, $closedCourseSetsNum, $unPublishedCourseSetsNum) = $this->getDifferentCourseSetsNum($conditions);

        $classrooms = array();
        $vips       = array();
        if ($filter == 'classroom') {
            $classrooms = $this->getClassroomService()->findClassroomCourseByCourseSetIds($courseSetIds);
            $classrooms = ArrayToolkit::index($classrooms, 'courseSetId');

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

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!isset($courseSetting['live_course_enabled'])) {
            $courseSetting['live_course_enabled'] = "";
        }

        $default = $this->getSettingService()->get('default', array());

        return $this->render('admin/course-set/index.html.twig', array(
            'conditions'               => $conditions,
            'courseSets'               => $courseSets,
            'defaultCourses'           => $defaultCourses,
            'users'                    => $users,
            'categories'               => $categories,
            'paginator'                => $paginator,
            'liveSetEnabled'           => $courseSetting['live_course_enabled'],
            'default'                  => $default,
            'classrooms'               => $classrooms,
            'filter'                   => $filter,
            'vips'                     => $vips,
            'searchCourseSetsNum'      => $searchCourseSetsNum,
            'publishedCourseSetsNum'   => $publishedCourseSetsNum,
            'closedCourseSetsNum'      => $closedCourseSetsNum,
            'unPublishedCourseSetsNum' => $unPublishedCourseSetsNum
        ));
    }

    protected function getDifferentCourseSetsNum($conditions)
    {
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );

        $searchCourseSetsNum      = 0;
        $publishedCourseSetsNum   = 0;
        $closedCourseSetsNum      = 0;
        $unPublishedCourseSetsNum = 0;
        $searchCourseSetsNum      = count($courseSets);

        foreach ($courseSets as $courseSet) {
            if ($courseSet['status'] == 'published') {
                $publishedCourseSetsNum++;
            }

            if ($courseSet['status'] == 'closed') {
                $closedCourseSetsNum++;
            }

            if ($courseSet['status'] == 'draft') {
                $unPublishedCourseSetsNum++;
            }
        }

        return array($searchCourseSetsNum, $publishedCourseSetsNum, $closedCourseSetsNum, $unPublishedCourseSetsNum);
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
    public function deleteAction(Request $request, $id, $type)
    {
        $currentUser = $this->getUser();

        if (!$currentUser->hasPermission('admin_course_set_delete')) {
            throw $this->createAccessDeniedException('您没有删除课程的权限！');
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        if ($courseSet['status'] == 'published') {
            $this->getCourseSetService()->closeCourseSet($id);
            $courseSet['status'] = 'closed';
        }

        $subCourses = $this->getCourseSetService()->findCourseSetsByParentIdAndLocked($id, 1);
        if (!empty($subCourses)) {
            return $this->createJsonResponse(array('code' => 2, 'message' => '请先删除班级课程'));
        }

        if ($courseSet['status'] == 'draft') {
            $result = $this->getCourseSetService()->deleteCourseSet($id);
            return $this->createJsonResponse(array('code' => 0, 'message' => '删除课程成功'));
        }

        if ($courseSet['status'] == 'closed') {
            $classroomCourse = $this->getClassroomService()->findClassroomIdsByCourseId($courseSet['id']);

            if ($classroomCourse) {
                return $this->createJsonResponse(array('code' => 3, 'message' => '当前课程未移除,请先移除班级课程'));
            }

            //判断作业插件版本号
            $homework = $this->getAppService()->findInstallApp("Homework");

            if (!empty($homework)) {
                $isDeleteHomework = $homework && version_compare($homework['version'], "1.3.1", ">=");

                if (!$isDeleteHomework) {
                    return $this->createJsonResponse(array('code' => 1, 'message' => '作业插件未升级'));
                }
            }

            if ($type) {
                $isCheckPassword = $request->getSession()->get('checkPassword');

                if (!$isCheckPassword) {
                    throw $this->createAccessDeniedException('未输入正确的校验密码！');
                }

                $result = $this->getCourseSetService()->deleteCourseSet($id);
                return $this->createJsonResponse($this->returnDeleteStatus($result, $type));
            }
        }

        return $this->render('admin/course/delete.html.twig', array('courseSet' => $courseSet));
    }

    public function checkPasswordAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $password    = $request->request->get('password');
            $currentUser = $this->getUser();
            $password    = $this->getPasswordEncoder()->encodePassword($password, $currentUser->salt);

            if ($password == $currentUser->password) {
                $response = array('success' => true, 'message' => '密码正确');
                $request->getSession()->set('checkPassword', true);
            } else {
                $response = array('success' => false, 'message' => '密码错误');
            }

            return $this->createJsonResponse($response);
        }
    }

    public function publishAction(Request $request, $id)
    {
        $this->getCourseSetService()->publishCourseSet($id);

        return $this->renderCourseTr($id, $request);
    }

    public function recommendAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);

        $ref    = $request->query->get('ref');
        $filter = $request->query->get('filter');

        if ($request->getMethod() == 'POST') {
            $number = $request->request->get('number');

            $courseSet = $this->getCourseSetService()->recommendCourse($id, $number);

            $user = $this->getUserService()->getUser($courseSet['creator']);

            if ($ref == 'recommendList') {
                return $this->render('admin/course-set/course-recommend-tr.html.twig', array(
                    'courseSet' => $courseSet,
                    'user'   => $user
                ));
            }

            return $this->renderCourseTr($id, $request);
        }

        return $this->render('admin/course-set/course-recommend-modal.html.twig', array(
            'courseSet' => $courseSet,
            'ref'    => $ref,
            'filter' => $filter
        ));
    }

    public function cancelRecommendAction(Request $request, $id, $target)
    {
        $courseSet = $this->getCourseSetService()->cancelRecommendCourse($id);

        if ($target == 'recommend_list') {
            return $this->createJsonResponse(array('success'=>1));
        }

        if ($target == 'normal_index') {
            return $this->renderCourseTr($id, $request);
        }
    }

    public function recommendListAction(Request $request)
    {
        $conditions                = $request->query->all();
        $conditions['status']      = 'published';
        $conditions['recommended'] = 1;

        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('recommendedSeq'=>'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        return $this->render('admin/course-set/course-recommend-list.html.twig', array(
            'courseSets'    => $courseSets,
            'users'      => $users,
            'paginator'  => $paginator,
            'categories' => $categories
        ));
    }

    public function dataAction(Request $request, $filter)
    {
        $conditions = $request->query->all();

        if ($filter == 'normal') {
            $conditions["parentId"] = 0;
        }

        if ($filter == 'classroom') {
            $conditions["parentId_GT"] = 0;
        }

        $conditions = $this->fillOrgCode($conditions);

        $count     = $this->getCourseSetService()->countCourseSets($conditions);
        $paginator = new Paginator($this->get('request'), $count, 20);

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $classrooms = array();

        if ($filter == 'classroom') {
            $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds(ArrayToolkit::column($courses, 'id'));
            $classrooms = ArrayToolkit::index($classrooms, 'courseId');

            foreach ($classrooms as $key => $classroom) {
                $classroomInfo                      = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        }

        $courseSetIncomes = $this->getCourseSetService()->findCourseSetIncomesByCourseSetIds($courseSetIds);
        $courseSetIncomes = ArrayToolkit::index($courseSetIncomes, 'courseSetId');

        foreach ($courseSets as $key => &$courseSet) {
            $courseSetId = $courseSet['id'];
            $courseCount = $this->getCourseService()->searchCourseCount(array('courseSetId' => $courseSetId));
            $isLearnedNum = $this->getMemberService()->countMembers(array('isLearned' => 1, 'courseSetId' => $courseSetId));
            $taskCount = $this->getCourseTaskService()->count(array('fromCourseSetId' => $courseSetId));

            $courseSet['learnedTime'] = $this->getCourseTaskService()->sumCourseSetLearnedTimeByCourseSetId($courseSetId);
            $courseSet['income'] = $courseSetIncomes[$courseSetId]['income'];
            $courseSet['isLearnedNum'] = $isLearnedNum;
            $courseSet['taskCount']  = $taskCount;
            $courseSet['courseCount'] = $courseCount;
        }

        return $this->render('admin/course-set/data.html.twig', array(
            'courseSets'    => $courseSets,
            'paginator'  => $paginator,
            'filter'     => $filter,
            'classrooms' => $classrooms
        ));
    }

    public function detailDataAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($id);
        $courseId = $request->query->get('courseId');

        if (empty($courseId)) {
            $courseId = $courses[0]['id'];            
        }

        $count     = $this->getCourseMemberService()->countMembers(array('courseId' => $courseId));

        $paginator = new Paginator($this->get('request'), $count, 20);

        $students = $this->getCourseMemberService()->findCourseStudents($courseId, $paginator->getOffsetCount(), $paginator->getPerPageCount());

        foreach ($students as $key => &$student) {
            $user                       = $this->getUserService()->getUser($student['userId']);
            $student['nickname'] = $user['nickname'];

            $questionCount                   = $this->getThreadService()->searchThreadCount(array('courseId' => $courseId, 'type' => 'question', 'userId' => $user['id']));
            $student['questionCount'] = $questionCount;

            if ($student['finishedTime'] > 0) {
                $student['fininshDay'] = intval(($student['finishedTime']- $student['createdTime']) / (60 * 60 * 24));
            } else {
                $student['fininshDay'] = intval((time()- $student['createdTime']) / (60 * 60 * 24));
            }

            $student['learnTime'] = intval($student['lastLearnTime'] - $student['createdTime']);
        }

        return $this->render('admin/course-set/course-data-modal.html.twig', array(
            'courseSet'    => $courseSet,
            'courses'   => $courses,
            'paginator' => $paginator,
            'students'  => $students,
            'courseId' => $courseId
        ));
    }

    public function coursesDataAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        $courseId = $request->query->get('courseId');

        if (empty($courseId)) {
            $courseId = $courses[0]['id'];            
        }
        $tasks = $this->getCourseTaskService()->findTasksByCourseId($courseId);

        foreach ($tasks as $key => &$task) {

            $finishedNum = $this->getCourseTaskResultService()->countTaskResults(array('status' => 'finish', 'courseTaskId' => $task['id']));
            $studentNum = $this->getCourseTaskResultService()->countTaskResults(array('courseTaskId' => $task['id']));
            $learnedTime =  $this->getCourseTaskResultService()->getLearnedTimeByCourseIdGroupByCourseTaskId($task['id']);
            if (in_array($task['type'], array('video','audio'))) {
                $activity = $this->getActivityService()->getActivity($task['activityId']);
                $task['length'] = $activity['length'];
                $task['watchTime'] = $this->getCourseTaskResultService()->getWatchTimeByCourseIdGroupByCourseTaskId($task['id']);
            } 

            if ($task['type'] == 'testpaper') {
                $activity = $this->getActivityService()->getActivity($task['activityId']);
                $score    = $this->getTestpaperService()->searchTestpapersScore(array('testId' => $activity['mediaId']));
                $paperNum = $this->getTestpaperService()->searchTestpaperResultsCount(array('testId' => $activity['mediaId']));

                $task['score'] = $paperNum == 0 ? 0 : intval($score / $paperNum);
            }

            $task['finishedNum'] = $finishedNum;
            $task['studentNum'] = $studentNum;

            $task['learnedTime'] = $learnedTime;
        }
 
        return $this->render('admin/course-set/course-list-data-modal.html.twig', array(
            'tasks'  => $tasks,
            'courseSet' => $courseSet,
            'courses' => $courses,
            'courseId' => $courseId
        ));
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
        $fields     = $request->query->all();
        $courseSet     = $this->getCourseSetService()->getCourseSet($courseId);
        $courseSet['defaultCourse'] =  $this->getCourseService()->getDefaultCourseByCourseSetId($courseId);
        $default    = $this->getSettingService()->get('default', array());
        $classrooms = array();
        $vips       = array();

        if ($fields['filter'] == 'classroom') {
            $classrooms = $this->getClassroomService()->findClassroomCourseByCourseSetIds(array($courseSet['id']));
            $classrooms = ArrayToolkit::index($classrooms, 'courseSetId');

            foreach ($classrooms as $key => $classroom) {
                $classroomInfo                      = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        } elseif ($fields['filter'] == 'vip') {
            if ($this->isPluginInstalled('Vip')) {
                $vips = $this->getVipLevelService()->searchLevels(array(), 0, PHP_INT_MAX);
                $vips = ArrayToolkit::index($vips, 'id');
            }
        }

        return $this->render('admin/course-set/tr.html.twig', array(
            'user'       => $this->getUserService()->getUser($courseSet['creator']),
            'category'   => isset($courseSet['categoryId']) ? $this->getCategoryService()->getCategory($courseSet['categoryId']) : array(),
            'courseSet'     => $courseSet,
            'default'    => $default,
            'classrooms' => $classrooms,
            'filter'     => $fields["filter"],
            'vips'       => $vips
        ));
    }

    protected function returnDeleteStatus($result, $type)
    {
        $dataDictionary = array('questions' => '问题', 'testpapers' => '试卷', 'materials' => '课时资料', 'chapters' => '课时章节', 'drafts' => '课时草稿', 'lessons' => '课时', 'lessonLearns' => '课时时长', 'lessonReplays' => '课时录播', 'lessonViews' => '课时播放时长', 'homeworks' => '课时作业', 'exercises' => '课时练习', 'favorites' => '课时收藏', 'notes' => '课时笔记', 'threads' => '课程话题', 'reviews' => '课程评价', 'announcements' => '课程公告', 'statuses' => '课程动态', 'members' => '课程成员', 'conversation' => '会话', 'course' => '课程');

        if ($result > 0) {
            $message = $dataDictionary[$type].'数据删除';
            return array('success' => true, 'message' => $message);
        } else {
            if ($type == "homeworks" || $type == "exercises") {
                $message = $dataDictionary[$type].'数据删除失败或插件未安装或插件未升级';
                return array('success' => false, 'message' => $message);
            } elseif ($type == 'course') {
                $message = $dataDictionary[$type].'数据删除';
                return array('success' => false, 'message' => $message);
            } else {
                $message = $dataDictionary[$type].'数据删除失败';
                return array('success' => false, 'message' => $message);
            }
        }
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseSetDeleteService()
    {
        return $this->createService('Course:CourseSetDeleteService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getPasswordEncoder()
    {
        return new MessageDigestPasswordEncoder('sha256');
    }

    protected function getVipLevelService()
    {
        return $this->createService('Vip:Vip.LevelService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getCourseTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }


    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }
}
