<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Visualization\Service\ActivityLearnDataService;
use Biz\Visualization\Service\CoursePlanLearnDataDailyStatisticsService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use Symfony\Component\HttpFoundation\Request;

class ClassroomController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $conditions = $this->fillOrgCode($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            20
        );

        $classroomInfo = $this->getClassroomService()->searchClassrooms(
            $conditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $classroomIds = ArrayToolkit::column($classroomInfo, 'id');

        $coinPriceAll = [];
        $priceAll = [];
        $classroomCoursesNum = [];

        $cashRate = $this->getCashRate();

        foreach ($classroomIds as $key => $value) {
            $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($value);
            $classroomCoursesNum[$value] = count($courses);

            $coinPrice = 0;
            $price = 0;

            foreach ($courses as $course) {
                $coinPrice += $course['originPrice'] * $cashRate;
                $price += $course['originPrice'];
            }

            $coinPriceAll[$value] = $coinPrice;
            $priceAll[$value] = $price;
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($classroomInfo, 'categoryId'));

        $classroomStatusNum = $this->getDifferentClassroomNum($conditions);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($classroomInfo, 'creator'));

        return $this->render('admin-v2/teach/classroom/index.html.twig', [
            'classroomInfo' => $classroomInfo,
            'paginator' => $paginator,
            'classroomStatusNum' => $classroomStatusNum,
            'classroomCoursesNum' => $classroomCoursesNum,
            'priceAll' => $priceAll,
            'coinPriceAll' => $coinPriceAll,
            'categories' => $categories,
            'users' => $users,
        ]);
    }

    public function setAction(Request $request)
    {
        $classroomSetting = $this->getSettingService()->get('classroom', []);

        $threadEnabled = $this->getSettingService()->node('ugc_thread.enable_thread', '1');
        $noteEnabled = $this->getSettingService()->node('ugc_note.enable_note', '1');
        $default = [
            'explore_default_orderBy' => 'createdTime',
            'show_review' => '1',
            'show_thread' => $threadEnabled &&
            ($this->getSettingService()->node('ugc_thread.enable_classroom_thread', '1') || $this->getSettingService()->node('ugc_thread.enable_classroom_question', '1'))
                ? '1' : '0',
            'show_note' => $noteEnabled ? $this->getSettingService()->node('ugc_note.enable_classroom_note', '1') : 0,
        ];

        $classroomSetting = array_merge($default, $classroomSetting);

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $classroomSetting = array_merge($classroomSetting, $set);

            $this->getSettingService()->set('classroom', $classroomSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/teach/classroom/set.html.twig', [
            'classroomSetting' => $classroomSetting,
        ]);
    }

    protected function getDifferentClassroomNum($conditions)
    {
        $total = $this->getClassroomService()->countClassrooms($conditions);
        $published = $this->getClassroomService()->countClassrooms(array_merge($conditions, ['status' => 'published']));
        $closed = $this->getClassroomService()->countClassrooms(array_merge($conditions, ['status' => 'closed']));
        $draft = $this->getClassroomService()->countClassrooms(array_merge($conditions, ['status' => 'draft']));

        return [
            'total' => empty($total) ? 0 : $total,
            'published' => empty($published) ? 0 : $published,
            'closed' => empty($closed) ? 0 : $closed,
            'draft' => empty($draft) ? 0 : $draft,
        ];
    }

    public function createAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '用户未登录，创建班级失败。');
        }

        if (!$user->isAdmin()) {
            return $this->createMessageResponse('info', 'message_response.only_admin_can_create_class.message');
        }

        if ('POST' == $request->getMethod()) {
            $myClassroom = $request->request->all();

            $title = trim($myClassroom['title']);

            $isClassroomExisted = $this->getClassroomService()->findClassroomByTitle($title);

            if (!empty($isClassroomExisted)) {
                $this->setFlashMessage('danger', 'classroom.create.title_not_unique');

                return $this->render('classroom/classroomadd.html.twig');
            }

            $classroom = [
                'title' => $myClassroom['title'],
            ];

            if (array_key_exists('orgCode', $myClassroom)) {
                $classroom['orgCode'] = $myClassroom['orgCode'];
            }

            $classroom = $this->getClassroomService()->addClassroom($classroom);

            return $this->redirect($this->generateUrl('classroom_manage', ['id' => $classroom['id']]));
        }

        return $this->render('classroom/classroomadd.html.twig');
    }

    public function closeAction($id)
    {
        $this->getClassroomService()->closeClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->renderClassroomTr($id, $classroom);
    }

    public function openAction($id)
    {
        $this->getClassroomService()->publishClassroom($id);

        $classroom = $this->getClassroomService()->getClassroom($id);

        return $this->renderClassroomTr($id, $classroom);
    }

    public function deleteAction(Request $request, $id)
    {
        $isCheckPasswordLifeTime = $request->getSession()->get('checkPassword');
        if (!$isCheckPasswordLifeTime || $isCheckPasswordLifeTime < time()) {
            return $this->render('admin-v2/teach/course/delete.html.twig', ['deleteUrl' => $this->generateUrl('admin_v2_classroom_delete', ['id' => $id])]);
        }
        $this->getClassroomService()->deleteClassroom($id);

        return $this->createJsonResponse(['code' => 0, 'message' => $this->trans('site.delete_success_hint')]);
    }

    public function checkEsProductCanDeleteAction(Request $request, $id)
    {
        $status = $this->getProductMallGoodsRelationService()->checkEsProductCanDelete([$id], 'classroom');
        return $this->createJsonResponse(['status' => $status]);
    }

    public function recommendAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);
        $ref = $request->query->get('ref');

        if ('POST' == $request->getMethod()) {
            $number = $request->request->get('number');

            $classroom = $this->getClassroomService()->recommendClassroom($id, $number);

            if ('recommendList' == $ref) {
                return $this->render('admin-v2/teach/classroom/recommend-tr.html.twig', [
                    'classroom' => $classroom,
                ]);
            }

            return $this->renderClassroomTr($id, $classroom);
        }

        return $this->render('admin-v2/teach/classroom/recommend-modal.html.twig', [
            'classroom' => $classroom,
            'ref' => $ref,
        ]);
    }

    public function cancelRecommendAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->cancelRecommendClassroom($id);
        $ref = $request->query->get('ref');

        if ('recommendList' == $ref) {
            return $this->render('admin-v2/teach/classroom/recommend-tr.html.twig', [
                'classroom' => $classroom,
            ]);
        }

        return $this->renderClassroomTr($id, $classroom);
    }

    public function recommendIndexAction()
    {
        $conditions = [
            'status' => 'published',
            'recommended' => 1,
        ];

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            20
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            ['recommendedSeq' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($classrooms, 'userId'));

        return $this->render('admin-v2/teach/classroom/recommend-list.html.twig', [
            'classrooms' => $classrooms,
            'users' => $users,
            'paginator' => $paginator,
            'ref' => 'recommendList',
        ]);
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

        $count = $this->getClassroomService()->countClassrooms($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $count,
            20
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            ['createdTime' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($classrooms, 'categoryId'));

        return $this->render('admin-v2/teach/classroom/classroom-chooser.html.twig', [
            'conditions' => $conditions,
            'classrooms' => $classrooms,
            'categories' => $categories,
            'paginator' => $paginator,
        ]);
    }

    public function statisticsAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->countClassrooms($conditions),
            20
        );

        $classrooms = $this->getClassroomService()->searchClassroomsWithStatistics(
            $conditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/teach/classroom/classroom-statistics.html.twig', [
            'classrooms' => $classrooms,
            'paginator' => $paginator,
        ]);
    }

    public function memberStatisticsAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);
        $memberCount = $this->getClassroomService()->getClassroomStudentCount($id);
        $paginator = new Paginator(
            $this->get('request'),
            $memberCount,
            20
        );

        $members = $this->getClassroomService()->findClassroomStudents($classroom['id'], $paginator->getOffsetCount(), $paginator->getPerPageCount());
        $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
        $classroomMembers = $this->getClassroomService()->searchMembers(['classroomId' => $classroom['id'], 'role' => 'student'], [], 0, $memberCount, ['userId']);

        $userIds = ArrayToolkit::column($members, 'userId');
        $users = empty($members) ? [] : $this->getUserService()->findUsersByIds($userIds);
        $totalLearnedTime = empty($classroomCourses) || empty($classroomMembers) ? 0 : $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeByConditions(['courseIds' => array_column($classroomCourses, 'id'), 'userIds' => array_column($classroomMembers, 'userId')]);

        $usersLearnedTime = [];
        if (!empty($users) && !empty($classroomCourses)) {
            $usersLearnedTime = $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeGroupByUserId([
                'userIds' => array_column($members, 'userId'), 'courseIds' => array_column($classroomCourses, 'id'),
            ]);
            $usersLearnedTime = array_column($usersLearnedTime, null, 'userId');
        }

        $usersProfile = empty($members) ? [] : $this->getUserService()->findUserProfilesByIds($userIds);
        $usersApproval = $this->getUserService()->searchApprovals([
            'userIds' => $userIds,
            'status' => 'approved',], [], 0, count($userIds));
        $usersApproval = ArrayToolkit::index($usersApproval, 'userId');

        foreach ($users as $key => &$user) {
            $user['mobile'] = isset($usersProfile[$key]['mobile']) ? $usersProfile[$key]['mobile'] : '';
            $user['idcard'] = isset($usersApproval[$key]['idcard']) ? $usersApproval[$key]['idcard'] : '';
        }

        foreach ($members as &$member) {
            $member['learnedTime'] = empty($usersLearnedTime[$member['userId']]) ? 0 : round($usersLearnedTime[$member['userId']]['learnedTime'] / 60, 1);
        }

        return $this->render('admin-v2/teach/classroom/classroom-member-statistics.html.twig', [
            'classroom' => $classroom,
            'members' => $members,
            'totalLearnedTime' => round($totalLearnedTime / 60, 1),
            'users' => $users,
            'paginator' => $paginator,
        ]);
    }

    public function courseStatisticsAction(Request $request, $id)
    {
        $classroom = $this->getClassroomService()->getClassroom($id);

        $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
        $courseId = $request->query->get('courseId');

        if (empty($courseId)) {
            $course = reset($classroomCourses);
            $courseId = $course['id'];
        }

        $paginator = new Paginator(
            $this->get('request'),
            empty($courseId) ? 0 : $this->getTaskService()->countTasks(['courseId' => $courseId]),
            20
        );

        $tasks = empty($courseId) ? [] : $this->getTaskService()->searchTasksWithStatistics(['courseId' => $courseId], ['id' => 'ASC'], $paginator->getOffsetCount(), $paginator->getPerPageCount());
        foreach ($tasks as &$task) {
            if ('video' == $task['type']) {
                $task['length'] = round($task['length'] / 60, 1);
            }
        }

        $totalLearnedTime = empty($courseId) ? 0 : $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeByCourseId($courseId);

        return $this->render(
            'admin-v2/teach/classroom/classroom-course-statistics.html.twig',
            [
                'classroom' => $classroom,
                'tasks' => $tasks,
                'courses' => $classroomCourses,
                'totalLearnedTime' => round($totalLearnedTime / 60, 1),
                'courseId' => $courseId,
                'paginator' => $paginator,
            ]
        );
    }

    private function renderClassroomTr($id, $classroom)
    {
        $coinPrice = 0;
        $price = 0;
        $coinPriceAll = [];
        $priceAll = [];
        $classroomCoursesNum = [];
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($id);
        $classroomCoursesNum[$id] = count($courses);
        $cashRate = $this->getCashRate();

        foreach ($courses as $course) {
            $coinPrice += $course['price'] * $cashRate;
            $price += $course['price'];
        }

        $coinPriceAll[$id] = $coinPrice;
        $priceAll[$id] = $price;

        return $this->render('admin-v2/teach/classroom/table-tr.html.twig', [
            'classroom' => $classroom,
            'classroomCoursesNum' => $classroomCoursesNum,
            'coinPriceAll' => $coinPriceAll,
            'priceAll' => $priceAll,
            'user' => $this->getUserService()->getUser($classroom['creator']),
        ]);
    }

    protected function getCashRate()
    {
        $coinSetting = $this->getSettingService()->get('coin');
        $coinEnable = isset($coinSetting['coin_enabled']) && 1 == $coinSetting['coin_enabled'];
        $cashRate = $coinEnable && isset($coinSetting['cash_rate']) ? $coinSetting['cash_rate'] : 1;

        return $cashRate;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CategoryService
     */
    private function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return CoursePlanLearnDataDailyStatisticsService
     */
    protected function getCoursePlanLearnDataDailyStatisticsService()
    {
        return $this->createService('Visualization:CoursePlanLearnDataDailyStatisticsService');
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
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return ActivityLearnDataService
     */
    protected function getActivityLearnDataService()
    {
        return $this->createService('Visualization:ActivityLearnDataService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    private function getProductMallGoodsRelationService()
    {
        return $this->createService('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
