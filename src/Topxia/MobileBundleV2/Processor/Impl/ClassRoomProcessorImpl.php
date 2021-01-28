<?php

namespace Topxia\MobileBundleV2\Processor\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Review\Service\ReviewService;
use Symfony\Component\HttpFoundation\Response;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\ClassRoomProcessor;

class ClassRoomProcessorImpl extends BaseProcessor implements ClassRoomProcessor
{
    public function after()
    {
        if (!class_exists('Biz\Classroom\Service\Impl\ClassroomServiceImpl')) {
            $this->stopInvoke();

            return $this->createErrorResponse('no_classroom', '没有安装班级插件！');
        }
    }

    public function search()
    {
        $conditions = [
            'status' => 'published',
            'private' => 0,
        ];

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $conditions['titleLike'] = $this->getParam('title');
        $total = $this->getClassroomService()->countClassrooms($conditions);
        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            ['recommendedSeq' => 'desc'],
            $start,
            $limit
        );

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->filterClassRooms($classrooms),
        ];
    }

    public function sign()
    {
        $classRoomId = $this->getParam('classRoomId', 0);
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能签到！');
        }

        $userSignStatistics = [];
        $member = $this->getClassroomService()->getClassroomMember($classRoomId, $user['id']);

        try {
            if ($this->getClassroomService()->canTakeClassroom($classRoomId) || (isset($member) && 'auditor' == $member['role'])) {
                $this->getSignService()->userSign($user['id'], 'classroom_sign', $classRoomId);

                $userSignStatistics = $this->getSignService()->getSignUserStatistics($user['id'], 'classroom_sign', $classRoomId);
            }
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        return [
            'isSignedToday' => true,
            'userSignStatistics' => $userSignStatistics,
        ];
    }

    public function getTodaySignInfo()
    {
        $classRoomId = $this->getParam('classRoomId', 0);
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看班级！');
        }
        $classroom = $this->getClassroomService()->getClassroom($classRoomId);

        $isSignedToday = $this->getSignService()->isSignedToday($user['id'], 'classroom_sign', $classroom['id']);

        $week = ['日', '一', '二', '三', '四', '五', '六'];

        $userSignStatistics = $this->getSignService()->getSignUserStatistics($user['id'], 'classroom_sign', $classroom['id']);

        $day = date('d', time());

        $signDay = $this->getSignService()->findSignRecordsByPeriod($user['id'], 'classroom_sign', $classroom['id'], date('Y-m', time()), date('Y-m-d', time() + 3600));
        $notSign = $day - count($signDay);

        if (!empty($userSignStatistics)) {
            $userSignStatistics['createdTime'] = date('c', $userSignStatistics['createdTime']);
        }

        return [
            'isSignedToday' => $isSignedToday,
            'userSignStatistics' => $userSignStatistics,
            'notSign' => $notSign,
            'week' => $week[date('w', time())],
        ];
    }

    public function getAnnouncements()
    {
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $classRoomId = $this->getParam('classRoomId', 0);
        if (empty($classRoomId)) {
            return [];
        }

        $conditions = [
            'targetType' => 'classroom',
            'targetId' => $classRoomId,
        ];

        $announcements = $this->getAnnouncementService()->searchAnnouncements($conditions, ['createdTime' => 'DESC'], $start, $limit);
        $announcements = array_values($announcements);

        return $this->filterAnnouncements($announcements);
    }

    public function getRecommendClassRooms()
    {
        $conditions = [
            'status' => 'published',
            'private' => 0,
        ];

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $total = $this->getClassroomService()->countClassrooms($conditions);
        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            ['recommendedSeq' => 'desc'],
            $start,
            $limit
        );

        $allClassrooms = [];
        for ($i = 0; $i < count($classrooms); ++$i) {
            if ($classrooms[$i]['recommendedTime'] > 0) {
                $allClassrooms[] = $classrooms[$i];
            }
        }

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->filterClassRooms($allClassrooms),
        ];
    }

    public function getLatestClassrooms()
    {
        $conditions = [
            'status' => 'published',
            'private' => 0,
        ];

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $total = $this->getClassroomService()->countClassrooms($conditions);
        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            ['createdTime' => 'desc'],
            $start,
            $limit
        );
        $allClassrooms = array_values($classrooms);

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->filterClassRooms($allClassrooms),
        ];
    }

    public function exitClassRoom($classRoomId, $user)
    {
        $member = $this->getClassroomService()->getClassroomMember($classRoomId, $user['id']);

        if (empty($member)) {
            return $this->createErrorResponse('error', '您不是班级的学员。');
        }

        if (!array_intersect($member['role'], ['auditor', 'student'])) {
            return $this->createErrorResponse('error', '您不是班级的学员。');
        }

        if (!empty($member['orderId'])) {
            return $this->createErrorResponse('error', '有关联的订单，不能直接退出学习。');
        }

        try {
            $this->getClassroomService()->exitClassroom($classRoomId, $user['id']);
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        return true;
    }

    public function unLearn()
    {
        $classRoomId = $this->getParam('classRoomId');
        $targetType = $this->getParam('targetType');

        if (!in_array($targetType, ['course', 'classroom'])) {
            return $this->createErrorResponse('error', '退出学习失败');
        }
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能学习班级！');
        }

        $member = $this->getClassroomService()->getClassroomMember($classRoomId, $user['id']);

        if (empty($member)) {
            return $this->createErrorResponse('error', '您不是班级的学员。');
        }

        $reason = $this->getParam('reason', '');

        try {
            $this->getClassroomService()->tryTakeClassroom($classRoomId);
            $this->getClassroomService()->removeStudent($classRoomId, $user['id'], ['reason' => $reason]);
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        return true;
    }

    public function getTeachers()
    {
        $classRoomId = $this->getParam('classRoomId', 0);
        $classroom = $this->getClassroomService()->getClassroom($classRoomId);
        if (empty($classroom)) {
            return $this->createErrorResponse('error', '班级不存在!');
        }
        $headTeacher = $this->getClassroomService()->findClassroomMembersByRole($classRoomId, 'headTeacher', 0, 1);
        $assistants = $this->getClassroomService()->findClassroomMembersByRole($classRoomId, 'assistant', 0, PHP_INT_MAX);
        $studentAssistants = $this->getClassroomService()->findClassroomMembersByRole($classRoomId, 'studentAssistant', 0, PHP_INT_MAX);
        $members = $this->getClassroomService()->findClassroomMembersByRole($classRoomId, 'teacher', 0, PHP_INT_MAX);
        $members = array_merge($headTeacher, $members, $assistants, $studentAssistants);
        $members = ArrayToolkit::index($members, 'userId');
        $teacherIds = ArrayToolkit::column($members, 'userId');
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);

        $sortTeachers = [];
        foreach ($members as $key => $member) {
            $teacher = $teachers[$member['userId']];
            $teacher['memberRole'] = $member['role'];
            $sortTeachers[] = $teacher;
        }

        return $this->controller->filterUsers($sortTeachers);
    }

    public function getStudents()
    {
        $classRoomId = $this->getParam('classRoomId', 0);
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $classroom = $this->getClassroomService()->getClassroom($classRoomId);
        if (empty($classroom)) {
            return $this->createErrorResponse('error', '班级不存在!');
        }

        $total = (int) $classroom['studentNum'];

        if (-1 == $limit) {
            $limit = $total;
        }
        $students = $this->getClassroomService()->findClassroomStudents($classRoomId, 0, $limit);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($students, 'userId'));

        $users = $this->controller->filterUsers($users);

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $classroom['studentNum'],
            'data' => array_values($users),
        ];
    }

    public function getReviews()
    {
        $classRoomId = $this->getParam('classRoomId', 0);

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $conditions = ['targetId' => $classRoomId, 'targetType' => 'classroom'];
        $total = $this->getReviewService()->countReviews($conditions);
        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );

        $reviews = $this->controller->filterReviews($reviews);

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $reviews,
        ];
    }

    public function getReviewInfo()
    {
        $classRoomId = $this->getParam('classRoomId', 0);
        $classroom = $this->getClassroomService()->getClassroom($classRoomId);

        $conditions = ['targetId' => $classRoomId, 'targetType' => 'classroom'];
        $total = $this->getReviewService()->countReviews($conditions);
        $reviews = $this->getReviewService()->searchReviews(
            $conditions,
            ['createdTime' => 'DESC'],
            0,
            $total
        );

        $progress = [0, 0, 0, 0, 0];
        foreach ($reviews as $key => $review) {
            $rating = $review['rating'] < 1 ? 1 : $review['rating'];
            ++$progress[$review['rating'] - 1];
        }

        return [
            'info' => [
                'ratingNum' => $classroom['ratingNum'],
                'rating' => $classroom['rating'],
            ],
            'progress' => $progress,
        ];
    }

    public function learnByVip()
    {
        $classRoomId = $this->getParam('classRoomId');
        if (!$this->controller->isinstalledPlugin('Vip') || !$this->controller->setting('vip.enabled')) {
            return $this->createErrorResponse('not_login', '网校未开启会员体系');
        }

        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能学习班级！');
        }
        try {
            list($success, $message) = $this->getVipFacadeService()->joinClassroom($classRoomId);
            if (!$success) {
                return $this->createErrorResponse('error', $message);
            }
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        return true;
    }

    public function getClassRoomMember()
    {
        $classRoomId = $this->getParam('classRoomId');
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看班级！');
        }
        if (empty($classRoomId)) {
            return null;
        }
        $member = $user ? $this->getClassroomService()->getClassroomMember($classRoomId, $user['id']) : null;
        if ($member && $member['locked']) {
            return null;
        }

        return empty($member) ? new Response('null') : $member;
    }

    public function getClassRoom()
    {
        $id = $this->getParam('id');
        $classroom = $this->getClassroomService()->getClassroom($id);

        $user = $this->controller->getUserByToken($this->request);
        $userId = empty($user) ? 0 : $user['id'];
        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $userId) : null;

        //老接口VIP加入，没有orderId
        if ($this->isUserVipExpire($classroom, $member)) {
            return $this->createErrorResponse('user.vip_expired', '会员已过期，请重新加入班级！');
        }

        $vipLevels = [];
        if ($this->controller->isinstalledPlugin('Vip') && $this->controller->setting('vip.enabled')) {
            $vipLevels = $this->controller->getLevelService()->searchLevels(
                [
                    'enabled' => 1,
                ],
                null,
                0,
                100
            );
        }

        $checkMemberLevelResult = null;
        if ($this->controller->isinstalledPlugin('Vip') && $this->controller->setting('vip.enabled')) {
            $classroomMemberLevel = $classroom['vipLevelId'] > 0 ? $this->controller->getLevelService()->getLevel($classroom['vipLevelId']) : null;
        }

        $teacherIds = $classroom['teacherIds'];
        $users = $this->controller->getUserService()->findUsersByIds(empty($teacherIds) ? [] : $teacherIds);
        $classroom['teachers'] = array_values($this->filterUsersFiled($users));

        return [
            'classRoom' => $this->filterClassRoom($classroom, false),
            'member' => $member,
            'vip' => $checkMemberLevelResult,
            'vipLevels' => $vipLevels,
        ];
    }

    private function filterClassRoom($classroom, $isList = true)
    {
        if (empty($classroom)) {
            return null;
        }

        $classrooms = $this->filterClassRooms([$classroom], $isList);

        return current($classrooms);
    }

    public function filterClassRooms($classrooms, $isList = true)
    {
        if (empty($classrooms)) {
            return [];
        }

        $coinSetting = $this->controller->getCoinSetting();
        $self = $this->controller;
        $container = $this->getContainer();

        return array_map(function ($classroom) use ($self, $container, $isList, $coinSetting) {
            $classroom['smallPicture'] = $container->get('web.twig.extension')->getFurl($classroom['smallPicture'], 'classroom.png');
            $classroom['middlePicture'] = $container->get('web.twig.extension')->getFurl($classroom['middlePicture'], 'classroom.png');
            $classroom['largePicture'] = $container->get('web.twig.extension')->getFurl($classroom['largePicture'], 'classroom.png');

            $classroom['recommendedTime'] = date('c', $classroom['recommendedTime']);
            $classroom['createdTime'] = date('c', $classroom['createdTime']);
            if ($isList) {
                $classroom['about'] = mb_substr($classroom['about'], 0, 20, 'utf-8');
            }
            $classroom['about'] = $self->convertAbsoluteUrl($container->get('request'), $classroom['about']);

            $service = $classroom['service'];
            if (!empty($service)) {
                $searchIndex = array_search('studyPlan', $service);
                if (false !== $searchIndex) {
                    array_splice($service, $searchIndex, 1);
                    $classroom['service'] = $service;
                }
            }

            if (!empty($coinSetting)) {
                $classroom['priceType'] = $coinSetting['priceType'];
                $classroom['coinName'] = $coinSetting['name'];
                $classroom['coinPrice'] = (string) ((float) $classroom['price'] * (float) $coinSetting['cashRate']);
            }

            return $classroom;
        }, $classrooms);
    }

    public function getClassRoomCourses()
    {
        $classroomId = $this->getParam('classRoomId');
        $user = $this->controller->getUserByToken($this->request);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            return $this->createErrorResponse('error', '没有找到该班级');
        }

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        return $this->controller->filterCourses($courses);
    }

    public function getClassRoomCoursesAndProgress()
    {
        $classroomId = $this->getParam('classRoomId');
        $user = $this->controller->getUserByToken($this->request);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            return $this->createErrorResponse('error', '没有找到该班级');
        }

        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        $progressArray = [];
        $user = $this->controller->getUserByToken($this->request);
        if ($user->isLogin()) {
            foreach ($courses as $key => $course) {
                $courseMember = $this->getCourseMemberService()->getCourseMember($course['id'], $user['id']);

                $lessonNum = (float) $course['taskNum'];
                $progress = 0 == $lessonNum ? 0 : (float) $courseMember['learnedNum'] / $lessonNum;

                $lastLesson = null;

                $userTasks = $this->getTaskResultService()->findUserTaskResultsByCourseId($course['id']);

                if (!$userTasks) {
                    break;
                }

                $latestTaskResult = end($userTasks);

                $lastTask = $this->getTaskService()->getTask($latestTaskResult['courseTaskId']);

                $progressArray[$course['id']] = [
                    'lastLesson' => $this->filterLastLearnLesson($lastTask),
                    'progress' => (int) ($progress * 100).'%',
                    'progressValue' => $progress,
                ];
            }
        }

        return [
            'courses' => $this->controller->filterCourses($courses),
            'progress' => $progressArray,
        ];
    }

    private function filterLastLearnLesson($lastLesson)
    {
        if (empty($lastLesson)) {
            return $lastLesson;
        }
        foreach ($lastLesson as $key => $value) {
            if (!in_array($key, ['id', 'title', 'courseId', 'fromCourseSetId', 'activityId', 'itemType'])) {
                unset($lastLesson[$key]);
            }
        }

        return $lastLesson;
    }

    public function myClassRooms()
    {
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看班级！');
        }
        $progresses = [];
        $classrooms = [];

        $studentClassrooms = $this->getClassroomService()->searchMembers(['role' => 'student', 'userId' => $user->id], ['createdTime' => 'desc'], 0, PHP_INT_MAX);
        $auditorClassrooms = $this->getClassroomService()->searchMembers(['role' => 'auditor', 'userId' => $user->id], ['createdTime' => 'desc'], 0, PHP_INT_MAX);

        $total = 0;
        $total += $this->getClassroomService()->searchMemberCount(['role' => 'student', 'userId' => $user->id], ['createdTime' => 'desc'], 0, PHP_INT_MAX);
        $total += $this->getClassroomService()->searchMemberCount(['role' => 'auditor', 'userId' => $user->id], ['createdTime' => 'desc'], 0, PHP_INT_MAX);

        $classrooms = array_merge($studentClassrooms, $auditorClassrooms);

        $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        foreach ($classrooms as $key => $classroom) {
            $courses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
            $coursesCount = count($courses);

            $classrooms[$key]['coursesCount'] = $coursesCount;

            $classroomId = [$classroom['id']];
            $member = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomId);
            $time = time() - $member[$classroom['id']]['createdTime'];
            $day = intval($time / (3600 * 24));

            $classrooms[$key]['day'] = $day;
            $progresses[$classroom['id']] = $this->calculateUserLearnProgress($classroom, $user->id);
        }

        $classrooms = $this->filterMyClassRoom($classrooms, $progresses);

        return [
            'start' => $start,
            'total' => $total,
            'limit' => $total,
            'data' => array_values($classrooms),
        ];
    }

    private function filterMyClassRoom($classrooms, $progresses)
    {
        $classrooms = $this->filterClassRooms($classrooms);

        return array_map(function ($classroom) use ($progresses) {
            $progresse = $progresses[$classroom['id']];
            $classroom['percent'] = $progresse['percent'];
            $classroom['number'] = $progresse['number'];
            $classroom['total'] = $progresse['total'];

            unset($classroom['description']);
            unset($classroom['about']);
            unset($classroom['teacherIds']);
            unset($classroom['service']);

            return $classroom;
        }, $classrooms);
    }

    private function calculateUserLearnProgress($classroom, $userId)
    {
        $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($classroom['id'], $userId);

        return [
            'percent' => $progress['percent'],
            'number' => $progress['finishedCount'],
            'total' => $progress['total'],
        ];
    }

    public function getClassRooms()
    {
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $category = $this->getParam('category', 0);

        $title = $this->getParam('title', '');
        $sort = $this->getParam('sort', 'createdTime');
        $conditions = [
            'status' => 'published',
            'title' => $title,
        ];

        if (!empty($category)) {
            $categoryArray = $this->getCategoryService()->getCategory($category);
            $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($categoryArray['id']);
            $categoryIds = array_merge($childrenIds, [$categoryArray['id']]);
            $conditions['categoryIds'] = $categoryIds;
        }

        $conditions['recommended'] = ('recommendedSeq' == $sort) ? 1 : null;
        $total = $this->getClassroomService()->countClassrooms($conditions);

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            [$sort => 'desc'],
            $start,
            $limit
        );

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->filterClassRooms($classrooms),
        ];
    }

    private function getVipFacadeService()
    {
        return $this->controller->getService('VipPlugin:Vip:VipFacadeService');
    }

    private function getVipService()
    {
        return $this->controller->getService('VipPlugin:Vip:VipService');
    }

    private function getSignService()
    {
        return $this->controller->getService('Sign:SignService');
    }

    private function getCategoryService()
    {
        return $this->controller->getService('Taxonomy:CategoryService');
    }

    private function getClassroomService()
    {
        return $this->controller->getService('Classroom:ClassroomService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->controller->getService('Review:ReviewService');
    }

    protected function getCourseMemberService()
    {
        return $this->controller->getService('Course:MemberService');
    }

    protected function getTaskResultService()
    {
        return $this->controller->getService('Task:TaskResultService');
    }

    protected function getTaskService()
    {
        return $this->controller->getService('Task:TaskService');
    }

    /**
     * @return \Biz\Classroom\Service\LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->controller->getService('Classroom:LearningDataAnalysisService');
    }

    private function isUserVipExpire($classroom, $member)
    {
        if (!($this->controller->isinstalledPlugin('Vip') && $this->controller->setting('vip.enabled'))) {
            return false;
        }

        $user = $this->controller->getUserByToken($this->request);
        if ($user->isAdmin()) {
            return false;
        }

        if (!$member || array_intersect($member['role'], ['assistant', 'teacher', 'headTeacher'])) {
            return false;
        }

        //老VIP加入接口加入进来的用户
        if ($classroom['vipLevelId'] > 0 && ((0 == $member['orderId'] && 0 == $member['levelId']) || $member['levelId'] > 0)) {
            $userVipStatus = $this->getVipService()->checkUserInMemberLevel(
                $member['userId'],
                $classroom['vipLevelId']
            );

            return 'ok' !== $userVipStatus;
        }

        return false;
    }
}
