<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ExportHelp;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class CourseController extends BaseController
{
    public function indexAction(Request $request, $filter)
    {
        $conditions = $request->query->all();
        if ('normal' == $filter) {
            $conditions['parentId'] = 0;
        }

        if ('classroom' == $filter) {
            $conditions['parentId_GT'] = 0;
        }

        if ('vip' == $filter) {
            $conditions['vipLevelIdGreaterThan'] = 1;
            $conditions['parentId'] = 0;
        }

        foreach (array('categoryId', 'status', 'title', 'creator') as $value) {
            if (isset($conditions[$value]) && '' == $conditions[$value]) {
                unset($conditions[$value]);
            }
        }

        $conditions = $this->fillOrgCode($conditions);

        $coinSetting = $this->getSettingService()->get('coin');
        $coinEnable = isset($coinSetting['coin_enabled']) && 1 == $coinSetting['coin_enabled'] && 'currency' == $coinSetting['cash_model'];

        if (isset($conditions['chargeStatus'])) {
            if ('free' == $conditions['chargeStatus']) {
                $conditions['price'] = '0.00';
            } elseif ('charge' == $conditions['chargeStatus']) {
                $conditions['price_GT'] = '0.00';
            }
        }

        $count = $this->getCourseSetService()->countCourseSets($conditions);

        $paginator = new Paginator($request, $count, 20);
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $defaultCourses = $this->getCourseService()->getDefaultCoursesByCourseSetIds($courseSetIds);
        $defaultCourses = ArrayToolkit::index($defaultCourses, 'courseSetId');

        foreach ($courseSets as &$courseSet) {
            $courseSet['defaultCourse'] = $defaultCourses[$courseSet['id']];
        }

        list($searchCourseSetsNum, $publishedCourseSetsNum, $closedCourseSetsNum, $unPublishedCourseSetsNum) = $this->getDifferentCourseSetsNum($conditions);

        $classrooms = array();
        $vips = array();
        if ('classroom' == $filter) {
            $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds($courseSetIds);
            $classrooms = ArrayToolkit::index($classrooms, 'courseId');

            foreach ($classrooms as $key => $classroom) {
                $classroomInfo = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        } elseif ('vip' == $filter) {
            if ($this->isPluginInstalled('Vip')) {
                $vips = $this->getVipLevelService()->searchLevels(array(), 0, PHP_INT_MAX);
                $vips = ArrayToolkit::index($vips, 'id');
            }
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!isset($courseSetting['live_course_enabled'])) {
            $courseSetting['live_course_enabled'] = '';
        }

        $default = $this->getSettingService()->get('default', array());

        return $this->render('admin/course-set/index.html.twig', array(
            'conditions' => $conditions,
            'courseSets' => $courseSets,
            'defaultCourses' => $defaultCourses,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator,
            'liveSetEnabled' => $courseSetting['live_course_enabled'],
            'default' => $default,
            'classrooms' => $classrooms,
            'filter' => $filter,
            'vips' => $vips,
            'searchCourseSetsNum' => $searchCourseSetsNum,
            'publishedCourseSetsNum' => $publishedCourseSetsNum,
            'closedCourseSetsNum' => $closedCourseSetsNum,
            'unPublishedCourseSetsNum' => $unPublishedCourseSetsNum,
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

        $searchCourseSetsNum = 0;
        $publishedCourseSetsNum = 0;
        $closedCourseSetsNum = 0;
        $unPublishedCourseSetsNum = 0;
        $searchCourseSetsNum = count($courseSets);

        foreach ($courseSets as $courseSet) {
            if ('published' == $courseSet['status']) {
                ++$publishedCourseSetsNum;
            }

            if ('closed' == $courseSet['status']) {
                ++$closedCourseSetsNum;
            }

            if ('draft' == $courseSet['status']) {
                ++$unPublishedCourseSetsNum;
            }
        }

        return array($searchCourseSetsNum, $publishedCourseSetsNum, $closedCourseSetsNum, $unPublishedCourseSetsNum);
    }

    protected function searchFuncUsedBySearchActionAndSearchToFillBannerAction(Request $request, $twigToRender)
    {
        $key = $request->query->get('key');

        $conditions = array('title' => $key);
        $conditions['status'] = 'published';
        $conditions['type'] = 'normal';
        $conditions['parentId'] = 0;

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($request, $count, 6);

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render($twigToRender, array(
            'key' => $key,
            'courses' => $courses,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator,
        ));
    }

    public function searchAction(Request $request)
    {
        return $this->searchFuncUsedBySearchActionAndSearchToFillBannerAction(
            $request,
            'admin/course/search.html.twig'
        );
    }

    public function searchToFillBannerAction(Request $request)
    {
        return $this->searchFuncUsedBySearchActionAndSearchToFillBannerAction(
            $request,
            'admin/course/search-to-fill-banner.html.twig'
        );
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
    }

    public function publishAction(Request $request, $id)
    {
        $this->getCourseService()->publishCourse($id);

        return $this->renderCourseTr($id, $request);
    }

    public function closeAction(Request $request, $id)
    {
        $this->getCourseService()->closeCourse($id);

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
                return $this->render('admin/course-set/course-recommend-tr.html.twig', array(
                    'courseSet' => $courseSet,
                    'user' => $user,
                ));
            }

            return $this->renderCourseTr($id, $request);
        }

        return $this->render('admin/course-set/course-recommend-modal.html.twig', array(
            'courseSet' => $courseSet,
            'ref' => $ref,
            'filter' => $filter,
        ));
    }

    public function cancelRecommendAction(Request $request, $id, $target)
    {
        $courseSet = $this->getCourseSetService()->cancelRecommendCourse($id);

        if ('recommend_list' == $target) {
            return $this->forward('AppBundle:Admin/admin/course/recommendList', array(
                'request' => $request,
            ));
        }

        if ('normal_index' == $target) {
            return $this->renderCourseTr($id, $request);
        }
    }

    public function recommendListAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['recommended'] = 1;

        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $request,
            $this->getCourseSetService()->countCourseSets($conditions),
            20
        );

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('recommended' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        return $this->render('admin/course-set/course-recommend-list.html.twig', array(
            'courseSets' => $courseSets,
            'users' => $users,
            'paginator' => $paginator,
            'categories' => $categories,
        ));
    }

    public function categoryAction(Request $request)
    {
        return $this->forward('AppBundle:Admin/Category:embed', array(
            'group' => 'course',
            'layout' => 'admin/layout.html.twig',
        ));
    }

    public function prepareForExportTasksDatasAction(Request $request, $courseId)
    {
        if (empty($courseId)) {
            return $this->createJsonResponse(array('error' => 'courseId can not be null'));
        }

        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        list($title, $lessons, $courseTasksCount) = $this->getExportTasksDatas(
            $courseId,
            $start,
            $limit,
            $exportAllowCount
        );

        $file = '';
        if (0 == $start) {
            $file = ExportHelp::addFileTitle($request, 'course_tasks', $title);
        }

        $datas = implode("\r\n", $lessons);
        $fileName = ExportHelp::saveToTempFile($request, $datas, $file);

        $method = ExportHelp::getNextMethod($start + $limit, $courseTasksCount);

        return $this->createJsonResponse(
            array(
                'method' => $method,
                'fileName' => $fileName,
                'start' => $start + $limit,
            )
        );
    }

    protected function getExportTasksDatas($courseId, $start, $limit, $exportAllowCount)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $conditions = array(
            'courseId' => $courseId,
        );
        $courseTasksCount = $this->getTaskService()->countTasks($conditions);

        $courseTasksCount = ($courseTasksCount > $exportAllowCount) ? $exportAllowCount : $courseTasksCount;

        $titles = '任务名,学习人数,完成人数,平均学习时长(分),音视频时长(分),音视频平均观看时长(分),测试平均得分';

        $originaTasks = $this->makeTasksDatasByCourseId($courseId, $start, $limit);

        $exportTasks = array();
        foreach ($originaTasks as $task) {
            $exportTask = '';

            if ('text' == $task['type']) {
                $exportTask .= $task['title'] ? $task['title'].'(图文),' : '-'.',';
            } elseif ('video' == $task['type']) {
                $exportTask .= $task['title'] ? $task['title'].'(视频),' : '-'.',';
            } elseif ('audio' == $task['type']) {
                $exportTask .= $task['title'] ? $task['title'].'(音频),' : '-'.',';
            } elseif ('testpaper' == $task['type']) {
                $exportTask .= $task['title'] ? $task['title'].'(试卷),' : '-'.',';
            } elseif ('ppt' == $task['type']) {
                $exportTask .= $task['title'] ? $task['title'].'(ppt),' : '-'.',';
            } else {
                $exportTask .= $task['title'] ? $task['title'].',' : '-'.',';
            }

            $exportTask .= $task['studentNum'] ? $task['studentNum'].',' : '-'.',';
            $exportTask .= $task['finishedNum'] ? $task['finishedNum'].',' : '-'.',';

            $studentNum = (int) $task['studentNum'];
            $learnedTime = $studentNum ? floor((int) $task['learnedTime'] / $studentNum) : (int) $task['learnedTime'];
            $watchTime = empty($task['watchTime']) ? '' : ($studentNum ? floor((int) $task['watchTime'] / $studentNum) : (int) $task['watchTime']);

            $exportTask .= $learnedTime ? $learnedTime.',' : '-'.',';

            $exportTask .= !empty($task['length']) ? $task['length'].',' : '-'.',';

            $exportTask .= $watchTime ? $watchTime.',' : '-'.',';

            $exportTask .= !empty($task['score']) ? $task['score'].',' : '-'.',';

            $exportTasks[] = $exportTask;
        }

        return array($titles, $exportTasks, $courseTasksCount);
    }

    public function exportTaskDatasAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        if (empty($course)) {
            return $this->createJsonResponse(array('error' => 'course can not be found'));
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $courseTitle = 1 == $course['isDefault'] ? $courseSet['title'] : $courseSet['title'].'-'.$course['title'];
        $fileName = sprintf('%s-(%s).csv', $courseTitle, date('Y-n-d'));

        return ExportHelp::exportCsv($request, $fileName);
    }

    protected function makeTasksDatasByCourseId($courseId, $start = 0, $limit = 1000)
    {
        $tasks = $this->getTaskService()->searchTasks(array('courseId' => $courseId), array('id' => 'ASC'), $start, $limit);
        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true);
        $activities = ArrayToolkit::index($activities, 'id');

        array_walk(
            $tasks,
            function (&$task) use ($activities) {
                $task['activity'] = $activities[$task['activityId']];
            }
        );
        usort($tasks, function ($a, $b) {
            return $a['seq'] > $b['seq'];
        });
        $tasks = $this->taskDataStatistics($tasks);

        return $tasks;
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

        if (isset($conditions['creator']) && '' == $conditions['creator']) {
            unset($conditions['creator']);
        }

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($request, $count, 20);

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render('admin/course/course-set-chooser.html.twig', array(
            'conditions' => $conditions,
            'courses' => $courses,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator,
        ));
    }

    public function coursesDataAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        $courseId = $request->query->get('courseId');

        if (empty($courseId)) {
            $course = reset($courses);
            $courseId = $course['id'];
        }
        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($courseId);

        usort($tasks, function ($a, $b) {
            return $a['seq'] > $b['seq'];
        });

        $tasks = $this->taskDataStatistics($tasks);

        return $this->render(
            'admin/course-set/course-list-data-modal.html.twig',
            array(
                'tasks' => $tasks,
                'courseSet' => $courseSet,
                'courses' => $courses,
                'courseId' => $courseId,
            )
        );
    }

    //统计课程任务数据
    protected function taskDataStatistics($tasks)
    {
        foreach ($tasks as $key => &$task) {
            $finishedNum = $this->getTaskResultService()->countTaskResults(
                array('status' => 'finish', 'courseTaskId' => $task['id'])
            );
            $studentNum = $this->getTaskResultService()->countTaskResults(array('courseTaskId' => $task['id']));
            $learnedTime = $this->getTaskResultService()->getLearnedTimeByCourseIdGroupByCourseTaskId($task['id']);

            if (in_array($task['type'], array('video', 'audio'))) {
                $task['length'] = (int) ($task['length'] / 60);
                $watchTime = $this->getTaskResultService()->getWatchTimeByCourseIdGroupByCourseTaskId($task['id']);
                $task['watchTime'] = round($watchTime / 60);
            }

            if ('testpaper' == $task['type'] && !empty($task['activity'])) {
                $activity = $task['activity'];
                $score = $this->getTestpaperService()->searchTestpapersScore(array('testId' => $activity['ext']['mediaId']));
                $paperNum = $this->getTestpaperService()->searchTestpaperResultsCount(
                    array('testId' => $activity['ext']['mediaId'])
                );

                $task['score'] = 0 == $paperNum ? 0 : intval($score / $paperNum);
            }

            $task['finishedNum'] = $finishedNum;
            $task['studentNum'] = $studentNum;

            $task['learnedTime'] = round($learnedTime / 60);
        }

        return $tasks;
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
            $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds(array($course['id']));
            $classrooms = ArrayToolkit::index($classrooms, 'courseId');

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

        return $this->render('admin/course-set/tr.html.twig', array(
            'user' => $this->getUserService()->getUser($courseSet['creator']),
            'category' => isset($courseSet['categoryId']) ? $this->getCategoryService()->getCategory($courseSet['categoryId']) : array(),
            'courseSet' => $courseSet,
            'default' => $default,
            'classrooms' => $classrooms,
            'filter' => $fields['filter'],
            'vips' => $vips,
        ));
    }

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

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseCopyService()
    {
        return $this->createService('Course:CourseCopyService');
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
        return $this->createService('VipPlugin:Vip:LevelService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

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
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
