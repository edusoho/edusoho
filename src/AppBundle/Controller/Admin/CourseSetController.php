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

        foreach (array("categoryId", "status", "title", "creator") as $value) {
            if (isset($conditions[$value]) && $conditions[$value] == "") {
                unset($conditions[$value]);
            }
        }

        $conditions = $this->fillOrgCode($conditions);

        $coinSetting = $this->getSettingService()->get("coin");
        $coinEnable  = isset($coinSetting["coin_enabled"]) && $coinSetting["coin_enabled"] == 1 && $coinSetting['cash_model'] == 'currency';

        if (isset($conditions["chargeStatus"])) {
            if ($conditions["chargeStatus"] == "free") {
                $conditions['price'] = '0.00';
            } elseif ($conditions["chargeStatus"] == "charge") {
                $conditions['price_GT'] = '0.00';
            }
        }

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
            $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds($courseSetIds);
            $classrooms = ArrayToolkit::index($classrooms, 'courseId');

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

    protected function searchFuncUsedBySearchActionAndSearchToFillBannerAction(Request $request, $twigToRender)
    {
        $key = $request->request->get("key");

        $conditions             = array("title" => $key);
        $conditions['status']   = 'published';
        $conditions['type']     = 'normal';
        $conditions['parentId'] = 0;

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 6);

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render($twigToRender, array(
            'key'        => $key,
            'courses'    => $courses,
            'users'      => $users,
            'categories' => $categories,
            'paginator'  => $paginator
        ));
    }

    public function searchAction(Request $request)
    {
        return $this->searchFuncUsedBySearchActionAndSearchToFillBannerAction($request, 'admin/course/search.html.twig');
    }

    public function searchToFillBannerAction(Request $request)
    {
        return $this->searchFuncUsedBySearchActionAndSearchToFillBannerAction($request, 'admin/course/search-to-fill-banner.html.twig');
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

    public function closeAction(Request $request, $id)
    {
        $this->getCourseSetService()->closeCourseSet($id);
        return $this->renderCourseTr($id, $request);
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
            array('recommended'=>'desc'),
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

    public function categoryAction(Request $request)
    {
        return $this->forward('AppBundle:Admin/Category:embed', array(
            'group'  => 'course',
            'layout' => 'admin/layout.html.twig'
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

        if (isset($conditions["title"]) && $conditions["title"] == "") {
            unset($conditions["title"]);
        }

        if (isset($conditions["creator"]) && $conditions["creator"] == "") {
            unset($conditions["creator"]);
        }

        $conditions = $this->fillOrgCode($conditions);

        $count     = $this->getCourseService()->searchCourseCount($conditions);
        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $classrooms = array();

        if ($filter == 'classroom') {
            $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds(ArrayToolkit::column($courses, 'id'));
            $classrooms = ArrayToolkit::index($classrooms, 'courseId');

            foreach ($classrooms as $key => $classroom) {
                $classroomInfo                      = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        }

        foreach ($courses as $key => $course) {
            $isLearnedNum = $this->getCourseMemberService()->countMembers(array('isLearned' => 1, 'courseId' => $course['id']));

            $learnTime = $this->getCourseService()->searchLearnTime(array('courseId' => $course['id']));

            $lessonCount = $this->getCourseService()->searchLessonCount(array('courseId' => $course['id']));

            $courses[$key]['isLearnedNum'] = $isLearnedNum;
            $courses[$key]['learnTime']    = $learnTime;
            $courses[$key]['lessonCount']  = $lessonCount;
        }

        return $this->render('admin/course/data.html.twig', array(
            'courses'    => $courses,
            'paginator'  => $paginator,
            'filter'     => $filter,
            'classrooms' => $classrooms
        ));
    }

    public function lessonDataAction($id)
    {
        $course = $this->getCourseService()->tryManageCourse($id, 'admin_course_data');

        $lessons = $this->getCourseService()->searchLessons(array('courseId' => $id), array('createdTime', 'ASC'), 0, 1000);

        foreach ($lessons as $key => $value) {
            $lessonLearnedNum = $this->getCourseService()->findLearnsCountByLessonId($value['id']);

            $finishedNum = $this->getCourseService()->searchLearnCount(array('status' => 'finished', 'lessonId' => $value['id']));

            $lessonLearnTime = $this->getCourseService()->searchLearnTime(array('lessonId' => $value['id']));
            $lessonLearnTime = $lessonLearnedNum == 0 ? 0 : intval($lessonLearnTime / $lessonLearnedNum);

            $lessonWatchTime = $this->getCourseService()->searchWatchTime(array('lessonId' => $value['id']));
            $lessonWatchTime = $lessonWatchTime == 0 ? 0 : intval($lessonWatchTime / $lessonLearnedNum);

            $lessons[$key]['LearnedNum']  = $lessonLearnedNum;
            $lessons[$key]['length']      = intval($lessons[$key]['length'] / 60);
            $lessons[$key]['finishedNum'] = $finishedNum;
            $lessons[$key]['learnTime']   = $lessonLearnTime;
            $lessons[$key]['watchTime']   = $lessonWatchTime;

            if ($value['type'] == 'testpaper') {
                $paperId  = $value['mediaId'];
                $score    = $this->getTestpaperService()->searchTestpapersScore(array('testId' => $paperId));
                $paperNum = $this->getTestpaperService()->searchTestpaperResultsCount(array('testId' => $paperId));

                $lessons[$key]['score'] = $finishedNum == 0 ? 0 : intval($score / $paperNum);
            }
        }

        return $this->render('admin/course/lesson-data.html.twig', array(
            'course'  => $course,
            'lessons' => $lessons
        ));
    }

    public function chooserAction(Request $request)
    {
        $conditions             = $request->query->all();
        $conditions["parentId"] = 0;

        if (isset($conditions["categoryId"]) && $conditions["categoryId"] == "") {
            unset($conditions["categoryId"]);
        }

        if (isset($conditions["status"]) && $conditions["status"] == "") {
            unset($conditions["status"]);
        }

        if (isset($conditions["title"]) && $conditions["title"] == "") {
            unset($conditions["title"]);
        }

        if (isset($conditions["creator"]) && $conditions["creator"] == "") {
            unset($conditions["creator"]);
        }

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render('admin/course/course-chooser.html.twig', array(
            'conditions' => $conditions,
            'courses'    => $courses,
            'users'      => $users,
            'categories' => $categories,
            'paginator'  => $paginator
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
            $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds(array($course['id']));
            $classrooms = ArrayToolkit::index($classrooms, 'courseId');

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
}
