<?php

namespace Biz\Classroom\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ClassroomToolkit;
use AppBundle\Common\TimeMachine;
use Biz\Accessor\AccessorInterface;
use Biz\BaseService;
use Biz\Certificate\Service\CertificateService;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Dao\ClassroomCourseDao;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\Classroom\Dao\ClassroomMemberDao;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ThreadService as CourseThreadService;
use Biz\Exception\UnableJoinException;
use Biz\Goods\GoodsEntityFactory;
use Biz\Goods\Mediator\ClassroomGoodsMediator;
use Biz\Goods\Service\GoodsService;
use Biz\Order\OrderException;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\LogService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Taxonomy\TagOwnerManager;
use Biz\Thread\Service\ThreadService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Order\Service\OrderService;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;
use VipPlugin\Biz\Vip\Service\VipService;

class ClassroomServiceImpl extends BaseService implements ClassroomService
{
    public function searchMembers($conditions, $orderBy, $start, $limit, array $columns = [])
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getClassroomMemberDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function searchMembersByClassroomId($classroomId, $conditions, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getClassroomMemberDao()->searchMembersByClassroomId($classroomId, $conditions, $start, $limit);
    }

    public function countMembersByClassroomId($classroomId, $conditions)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getClassroomMemberDao()->countMembersByClassroomId($classroomId, $conditions);
    }

    public function findClassroomsByIds(array $ids)
    {
        return ArrayToolkit::index($this->getClassroomDao()->findByIds($ids), 'id');
    }

    public function findProductIdAndGoodsIdsByIds($ids)
    {
        return $this->getClassroomDao()->findProductIdAndGoodsIdsByIds($ids);
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);
        if (empty($classroomCourses)) {
            return [];
        }

        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        if (empty($courses)) {
            return [];
        }

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        $courseNums = $this->getCourseService()->countCoursesGroupByCourseSetIds($courseSetIds);
        $courseNums = ArrayToolkit::index($courseNums, 'courseSetId');
        foreach ($courses as &$course) {
            $curCourseSet = $courseSets[$course['courseSetId']];
            $course['courseSet'] = $curCourseSet;
            $course['courseNum'] = $courseNums[$curCourseSet['id']]['courseNum'];
            $course['parentCourseSetId'] = $curCourseSet['parentId'];
        }

        $sortedCourses = [];
        $courses = ArrayToolkit::index($courses, 'id');
        foreach ($classroomCourses as $key => $classroomCourse) {
            $sortedCourses[$key] = $courses[$classroomCourse['courseId']];
            $sortedCourses[$key]['classroom_course_id'] = $classroomCourse['id'];
        }

        return $sortedCourses;
    }

    public function findMembersByUserIdAndClassroomIds($userId, $classroomIds)
    {
        $members = $this->getClassroomMemberDao()->findByUserIdAndClassroomIds($userId, $classroomIds);
        if (empty($members)) {
            return [];
        }

        return ArrayToolkit::index($members, 'classroomId');
    }

    public function getClassroom($id)
    {
        return $this->getClassroomDao()->get($id);
    }

    public function hitClassroom($id)
    {
        $classroom = $this->getClassroom($id);
        if (empty($classroom)) {
            return;
        }

        return $this->getClassroomDao()->wave([$classroom['id']], ['hitNum' => 1]);
    }

    public function searchClassrooms($conditions, $orderBy, $start, $limit, $columns = [], $withMarketingInfo = false)
    {
        $orderBy = $this->getOrderBys($orderBy);
        $conditions = $this->_prepareClassroomConditions($conditions);

        $classrooms = $this->getClassroomDao()->search($conditions, $orderBy, $start, $limit, $columns);
        if ($withMarketingInfo) {
            $relatedInfos = ArrayToolkit::index($this->findProductIdAndGoodsIdsByIds(ArrayToolkit::column($classrooms, 'id')), 'classroomId');
            foreach ($classrooms as &$classroom) {
                $classroom['productId'] = !empty($relatedInfos[$classroom['id']]) ? $relatedInfos[$classroom['id']]['productId'] : null;
                $classroom['goodsId'] = !empty($relatedInfos[$classroom['id']]) ? $relatedInfos[$classroom['id']]['goodsId'] : null;
            }
        }

        return $classrooms;
    }

    public function searchClassroomsWithStatistics($conditions, $orderBy, $start, $limit, $columns = [])
    {
        $orderBy = $this->getOrderBys($orderBy);
        $conditions = $this->_prepareClassroomConditions($conditions);

        $classrooms = $this->getClassroomDao()->search($conditions, $orderBy, $start, $limit, $columns);

        return $this->calClassroomsTaskNums($classrooms, true);
    }

    public function appendSpecsInfo($classrooms)
    {
        $classrooms = $this->getGoodsEntityFactory()->create('classroom')->fetchSpecs($classrooms);

        return $classrooms;
    }

    public function appendSpecInfo($classroom)
    {
        $classroom['spec'] = $this->getGoodsEntityFactory()->create('classroom')->getSpecsByTargetId($classroom['id']);
        $classroom['goodsId'] = empty($classroom['spec']) ? 0 : $classroom['spec']['goodsId'];
        $classroom['specsId'] = empty($classroom['spec']) ? 0 : $classroom['spec']['id'];

        return $classroom;
    }

    public function countClassrooms($conditions)
    {
        $conditions = $this->_prepareClassroomConditions($conditions);
        $count = $this->getClassroomDao()->count($conditions);

        return $count;
    }

    //@deprecated 一个courseId（注意：不是parentCourseId）只会对应一个classroomId
    public function findClassroomIdsByCourseId($courseId)
    {
        return $this->getClassroomCourseDao()->findClassroomIdsByCourseId($courseId);
    }

    public function findClassroomIdsByParentCourseId($parentCourseId)
    {
        return $this->getClassroomCourseDao()->findClassroomIdsByParentCourseId($parentCourseId);
    }

    public function findByClassroomId($classroomId)
    {
        return $this->getClassroomCourseDao()->findByClassroomId($classroomId);
    }

    /**
     * @param int $courseId
     *
     * @return array
     *
     * @deprecated
     */
    public function findClassroomsByCourseId($courseId)
    {
        $classroomIds = $this->findClassroomIdsByCourseId($courseId);

        return $this->findClassroomsByIds($classroomIds);
    }

    public function getClassroomByCourseId($courseId)
    {
        $classroomIds = $this->findClassroomIdsByCourseId($courseId);
        if (empty($classroomIds)) {
            return [];
        }
        $classroomId = array_shift($classroomIds);

        return $this->getClassroom($classroomId['classroomId']);
    }

    public function getClassroomCourseByCourseSetId($courseSetId)
    {
        return $this->getClassroomCourseDao()->getByCourseSetId($courseSetId);
    }

    public function findAssistants($classroomId)
    {
        $classroom = $this->getClassroom($classroomId);
        $assistants = $this->getClassroomMemberDao()->findAssistantsByClassroomId($classroomId);

        if (!$assistants) {
            return [];
        }

        $assistantIds = ArrayToolkit::column($assistants, 'userId');
        $oldAssistantIds = $classroom['assistantIds'] ?: [];

        if (!empty($oldAssistantIds)) {
            $orderAssistantIds = array_intersect($oldAssistantIds, $assistantIds);
            $orderAssistantIds = array_merge($orderAssistantIds, array_diff($assistantIds, $oldAssistantIds));
        } else {
            $orderAssistantIds = $assistantIds;
        }

        return $orderAssistantIds;
    }

    public function findTeachers($classroomId)
    {
        $teachers = $this->getClassroomMemberDao()->findTeachersByClassroomId($classroomId);

        if (!$teachers) {
            return [];
        }

        $classroom = $this->getClassroom($classroomId);
        $teacherIds = ArrayToolkit::column($teachers, 'userId');
        $oldTeacherIds = $classroom['teacherIds'] ?: [];

        if (!empty($oldTeacherIds)) {
            $orderTeacherIds = array_intersect($oldTeacherIds, $teacherIds);
            $orderTeacherIds = array_merge($orderTeacherIds, array_diff($teacherIds, $oldTeacherIds));
        } else {
            $orderTeacherIds = $teacherIds;
        }

        return $orderTeacherIds;
    }

    /**
     * @param $classroom
     *
     * @return mixed
     *
     * @throws \Exception
     *                    班级创建调用中介者构建商品和产品
     */
    public function addClassroom($classroom)
    {
        $title = trim($classroom['title']);

        if (empty($title)) {
            $this->createNewException(ClassroomException::EMPTY_TITLE());
        }

        $classroom = $this->fillOrgId($classroom);
        $userId = $this->getCurrentUser()->getId();
        $classroom['title'] = $this->purifyHtml($classroom['title'], true);
        $classroom['creator'] = $userId;
        $classroom['teacherIds'] = [];
        $classroom['expiryMode'] = 'forever';
        $classroom['expiryValue'] = 0;
        $classroom['showable'] = 1;
        $classroom['buyable'] = 1;

        $classroom = $this->getClassroomDao()->create($classroom);
        $this->getClassroomGoodsMediator()->onCreate($classroom);

        $this->dispatchEvent('classroom.create', $classroom);

        return $classroom;
    }

    public function addCoursesToClassroom($classroomId, $courseIds)
    {
        $this->tryManageClassroom($classroomId);
        $this->beginTransaction();
        try {
            $allExistingCourses = $this->findCoursesByClassroomId($classroomId);

            $existCourseIds = ArrayToolkit::column($allExistingCourses, 'parentId');

            $diff = array_diff($courseIds, $existCourseIds);
            $classroom = $this->getClassroom($classroomId);

            if (!empty($diff)) {
                $courses = $this->getCourseService()->findCoursesByIds($diff);
                $newCourseIds = [];

                foreach ($courses as $key => $course) {
                    $newCourse = $this->getCourseSetService()->copyCourseSet(
                        $classroomId,
                        $course['courseSetId'],
                        $course['id']
                    );
                    $newCourseIds[] = $newCourse['id'];

                    $infoData = [
                        'classroomId' => $classroom['id'],
                        'title' => $classroom['title'],
                        'courseSetId' => $newCourse['id'],
                        'courseSetTitle' => $newCourse['title'],
                    ];

                    $this->getLogService()->info(
                        'classroom',
                        'add_course',
                        "班级《{$classroom['title']}》(#{$classroom['id']})添加了课程《{$newCourse['title']}》(#{$newCourse['id']})",
                        $infoData
                    );
                }

                $this->setClassroomCourses($classroomId, $newCourseIds);
                $newTasks = $this->getTaskService()->findTasksByCourseIds($newCourseIds);
                $this->dispatchEvent('course.task.copy', $newTasks);
            }
            $this->dispatchEvent(
                'classroom.course.create',
                new Event($classroom, ['courseIds' => $courseIds, 'newCourseIds' => $newCourseIds ?? []])
            );

            $this->commit();

            return $this->findActiveCoursesByClassroomId($classroomId);
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function findClassroomByTitle($title)
    {
        return $this->getClassroomDao()->getByTitle($title);
    }

    public function findClassroomsByLikeTitle($title)
    {
        return $this->getClassroomDao()->findByLikeTitle($title);
    }

    public function updateClassroom($id, $fields)
    {
        $user = $this->getCurrentUser();

        $classroom = $this->getClassroom($id);
        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        unset($fields['tagIds']);
        $fields = $this->filterClassroomFields($fields);

        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        if (!$this->canUpdateClassroomExpiryDate($fields, $classroom)) {
            $this->createNewException(ClassroomException::FORBIDDEN_UPDATE_EXPIRY_DATE());
        }

        if (isset($fields['description'])) {
            $fields['description'] = $this->purifyHtml($fields['description'], true);
        }

        if (isset($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about'], true);
        }

        $fields = $this->fillOrgId($fields);

        $classroom = $this->getClassroomDao()->update($id, $fields);

        if (array_intersect(
            array_keys($fields),
            $this->getClassroomGoodsMediator()->normalFields
        )) {
            $this->getClassroomGoodsMediator()->onUpdateNormalData($classroom);
        }

        $arguments = $fields;

        $this->dispatchEvent(
            'classroom.update',
            new Event([
                'userId' => $user['id'],
                'classroom' => $classroom,
                'fields' => $arguments,
            ])
        );

        return $classroom;
    }

    public function updateClassroomInfo($id, $fields)
    {
        $classroom = $this->getClassroom($id);
        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        $tagIds = empty($fields['tagIds']) ? [] : $fields['tagIds'];
        $this->updateClassroomTags($id, $tagIds);
        $this->dispatchEvent('classroom.info.update', ['id' => $id]);

        return $this->updateClassroom($id, $fields);
    }

    protected function updateClassroomTags($classroomId, $tagIds)
    {
        $user = $this->getCurrentUser();

        $tagOwnerManager = new TagOwnerManager('classroom', $classroomId, $tagIds, $user['id']);
        $tagOwnerManager->update();
    }

    public function updateMembersDeadlineByClassroomId($classroomId, $deadline)
    {
        return $this->getClassroomMemberDao()->updateByClassroomIdAndRole($classroomId, 'student', [
            'deadline' => $deadline,
        ]);
    }

    protected function canUpdateMembersDeadline($classroom, $expiryMode)
    {
        return $expiryMode === $classroom['expiryMode'] && 'days' !== $expiryMode;
    }

    protected function canUpdateClassroomExpiryDate($fields, $classroom)
    {
        if (empty($fields['expiryMode']) && empty($fields['expiryValue'])) {
            return true;
        }

        if ('draft' === $classroom['status']) {
            return true;
        }

        if ($fields['expiryMode'] === $classroom['expiryMode']) {
            return true;
        }

        return false;
    }

    protected function filterClassroomFields($fields)
    {
        $fields = ArrayToolkit::parts($fields, [
            'rating',
            'ratingNum',
            'categoryId',
            'title',
            'subtitle',
            'status',
            'about',
            'description',
            'price',
            'smallPicture',
            'middlePicture',
            'largePicture',
            'headTeacherId',
            'teacherIds',
            'assistantIds',
            'hitNum',
            'auditorNum',
            'studentNum',
            'courseNum',
            'lessonNum',
            'electiveTaskNum',
            'compulsoryTaskNum',
            'threadNum',
            'postNum',
            'income',
            'createdTime',
            'private',
            'service',
            'maxRate',
            'buyable',
            'showable',
            'orgCode',
            'orgId',
            'expiryMode',
            'expiryValue',
            'tagIds',
        ]);

        if (isset($fields['expiryMode']) && 'date' === $fields['expiryMode']) {
            if ($fields['expiryValue'] < time()) {
                $this->createNewException(ClassroomException::EXPIRY_VALUE_LIMIT());
            }
        }

        if (isset($fields['about'])) {
            $fields['about'] = $this->purifyHtml($fields['about'], true);
        }

        return $fields;
    }

    public function isClassroomOverDue($classroomId)
    {
        $classroom = $this->getClassroom($classroomId);

        return 'date' === $classroom['expiryMode'] && $classroom['expiryValue'] < time();
    }

    public function updateMemberDeadlineByMemberId($memberId, $deadline)
    {
        $member = $this->getClassroomMemberDao()->update($memberId, $deadline);

        $this->dispatchEvent(
            'classroom.member.deadline.update',
            new Event([
                'userId' => $member['userId'],
                'deadline' => $deadline['deadline'],
                'classroomId' => $member['classroomId'],
            ])
        );

        return $this->getClassroomMemberDao()->update($memberId, $deadline);
    }

    public function updateMembersDeadlineByDay($classroomId, $userIds, $day, $waveType = 'plus')
    {
        $this->tryManageClassroom($classroomId);

        if ($this->checkDayAndWaveTypeForUpdateDeadline($classroomId, $userIds, $day, $waveType)) {
            $members = $this->findMembersByClassroomIdAndUserIds($classroomId, $userIds);
            $updateDeadlines = [];
            foreach ($members as $member) {
                $member['deadline'] = $member['deadline'] > 0 ? $member['deadline'] : time();
                $deadline = 'plus' === $waveType ? $member['deadline'] + $day * 24 * 60 * 60 : $member['deadline'] - $day * 24 * 60 * 60;
                $updateDeadlines[] = ['deadline' => $deadline];
            }
            $this->getClassroomMemberDao()->batchUpdate(array_column($members, 'id'), $updateDeadlines, 'id');

            $courses = $this->findCoursesByClassroomId($classroomId);
            foreach ($courses as $course) {
                $this->getCourseMemberService()->batchUpdateMemberDeadlinesByDay($course['id'], $userIds, $day, $waveType);
            }
        }
    }

    public function updateMembersDeadlineByDate($classroomId, $userIds, $date)
    {
        $this->tryManageClassroom($classroomId);

        $date = TimeMachine::isTimestamp($date) ? $date : strtotime($date . ' 23:59:59');
        if ($this->checkDeadlineForUpdateDeadline($classroomId, $userIds, $date)) {
            $members = $this->findMembersByClassroomIdAndUserIds($classroomId, $userIds);
            $updateDeadlines = [];
            foreach ($members as $member) {
                $updateDeadlines[] = ['deadline' => $date];
            }
            $this->getClassroomMemberDao()->batchUpdate(array_column($members, 'id'), $updateDeadlines, 'id');

            $courses = $this->findCoursesByClassroomId($classroomId);
            foreach ($courses as $course) {
                $this->getCourseMemberService()->batchUpdateMemberDeadlinesByDate($course['id'], $userIds, $date);
            }
        }
    }

    public function checkDayAndWaveTypeForUpdateDeadline($classroomId, $userIds, $day, $waveType = 'plus')
    {
        $classroom = $this->getClassroom($classroomId);
        if ('forever' == $classroom['expiryMode']) {
            return false;
        }
        $members = $this->searchMembers(
            ['userIds' => $userIds, 'classroomId' => $classroomId],
            ['deadline' => 'ASC'],
            0,
            1
        );
        if (empty($members)) {
            return false;
        }
        if ('minus' == $waveType) {
            $member = array_shift($members);
            $maxAllowMinusDay = intval(($member['deadline'] - time()) / (24 * 3600));
            if ($day > $maxAllowMinusDay) {
                return false;
            }
        }

        return true;
    }

    public function checkDeadlineForUpdateDeadline($classroomId, $userIds, $date)
    {
        return $date > time();
    }

    public function findWillOverdueClassrooms()
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $members = $this->getClassroomMemberDao()->findMembersByUserId($user['id']);
        $members = ArrayToolkit::index($members, 'classroomId');

        $classroomIds = ArrayToolkit::column($members, 'classroomId');
        $classrooms = $this->findClassroomsByIds($classroomIds);

        $shouldNotifyClassrooms = [];
        $shouldNotifyClassroomMembers = [];

        $currentTime = time();

        foreach ($classrooms as $classroom) {
            $member = $members[$classroom['id']];

            if ($classroom['expiryValue'] > 0 && 0 == $member['deadlineNotified'] && $currentTime < $member['deadline'] && (10 * 24 * 680 * 60 + $currentTime) > $member['deadline']) {
                $shouldNotifyClassrooms[] = $classroom;
                $shouldNotifyClassroomMembers[] = $member;
            }
        }

        return [$shouldNotifyClassrooms, $shouldNotifyClassroomMembers];
    }

    public function batchUpdateOrg($classroomIds, $orgCode)
    {
        if (!is_array($classroomIds)) {
            $classroomIds = [$classroomIds];
        }
        $fields = $this->fillOrgId(['orgCode' => $orgCode]);

        foreach ($classroomIds as $classroomId) {
            $this->getClassroomDao()->update($classroomId, $fields);
        }
    }

    public function waveClassroom($id, $field, $diff)
    {
        $fields = [
            'hitNum',
            'auditorNum',
            'studentNum',
            'courseNum',
            'lessonNum',
            'threadNum',
            'postNum',
            'noteNum',
        ];

        if (!in_array($field, $fields)) {
            $this->createNewException(ClassroomException::FORBIDDEN_WAVE());
        }

        return $this->getClassroomDao()->wave([$id], [$field => $diff]);
    }

    private function deleteAllCoursesInClass($id)
    {
        $courses = $this->findCoursesByClassroomId($id);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $this->deleteClassroomCourses($id, $courseIds);
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws \Exception
     */
    public function deleteClassroom($id)
    {
        try {
            $this->beginTransaction();
            $classroom = $this->getClassroom($id);
            if (empty($classroom)) {
                $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
            }

            if ('published' === $classroom['status']) {
                $this->createNewException(ClassroomException::FORBIDDEN_DELETE_NOT_DRAFT());
            }
            $this->tryManageClassroom($id, 'admin_classroom_delete');
            if ($this->getProductMallGoodsRelationService()->checkEsProductCanDelete([$id], 'classroom') === 'error') {
                throw $this->createServiceException('该产品已在营销商城中上架售卖，请将对应商品下架后再进行删除操作');
            }
            $this->deleteAllCoursesInClass($id);
            $this->getClassroomDao()->delete($id);
            $this->getClassroomGoodsMediator()->onDelete($classroom);
            $this->dispatchEvent('classroom.delete', $classroom);

            $this->commit();
        } catch (\Exception $exception) {
            $this->rollback();
            throw $exception;
        }

        return true;
    }

    /**
     * @todo 能否简化业务逻辑？
     */
    public function updateClassroomTeachers($id)
    {
        $courses = $this->findActiveCoursesByClassroomId($id);

        $oldTeacherIds = $this->findTeachers($id);
        $newTeacherIds = [];

        foreach ($courses as $key => $value) {
            $teachers = $this->getCourseMemberService()->findCourseTeachers($value['id']);
            $teacherIds = ArrayToolkit::column($teachers, 'userId');
            $newTeacherIds = array_merge($newTeacherIds, $teacherIds);
        }

        $newTeacherIds = array_unique($newTeacherIds);

        $newTeacherIds = array_filter($newTeacherIds, function ($newTeacherId) {
            return !empty($newTeacherId);
        });

        $deleteTeacherIds = array_diff($oldTeacherIds, $newTeacherIds);
        $addTeacherIds = array_diff($newTeacherIds, $oldTeacherIds);
        $addMembers = $this->findMembersByClassroomIdAndUserIds($id, $addTeacherIds);
        $deleteMembers = $this->findMembersByClassroomIdAndUserIds($id, $deleteTeacherIds);

        foreach ($addTeacherIds as $userId) {
            if (!empty($addMembers[$userId])) {
                if ('auditor' === $addMembers[$userId]['role'][0]) {
                    $addMembers[$userId]['role'][0] = 'teacher';
                } else {
                    $addMembers[$userId]['role'][] = 'teacher';
                }

                $this->getClassroomMemberDao()->update($addMembers[$userId]['id'], $addMembers[$userId]);
            } else {
                $this->becomeTeacher($id, $userId);
            }
        }

        foreach ($deleteTeacherIds as $userId) {
            if (1 === count($deleteMembers[$userId]['role'])) {
                $this->getClassroomMemberDao()->delete($deleteMembers[$userId]['id']);
            } else {
                foreach ($deleteMembers[$userId]['role'] as $key => $value) {
                    if ('teacher' === $value) {
                        unset($deleteMembers[$userId]['role'][$key]);
                    }
                }

                $this->getClassroomMemberDao()->update($deleteMembers[$userId]['id'], $deleteMembers[$userId]);
            }
        }

        $this->updateClassroom($id, ['teacherIds' => array_values($newTeacherIds)]);
    }

    public function publishClassroom($id)
    {
        $this->tryManageClassroom($id, 'admin_classroom_open');
        $classroom = $this->getClassroom($id);
        if (0 == $classroom['courseNum']) {
            $this->createNewException(ClassroomException::AT_LEAST_ONE_COURSE());
        }
        $classroom = $this->updateClassroom($id, ['status' => 'published']);

        $this->getClassroomGoodsMediator()->onPublish($classroom);
        $this->dispatchEvent('classroom.publish', new Event($classroom));

        return $classroom;
    }

    public function closeClassroom($id)
    {
        $this->tryManageClassroom($id, 'admin_classroom_close');

        $classroom = $this->updateClassroom($id, ['status' => 'closed']);
        $this->getClassroomGoodsMediator()->onClose($classroom);
        $this->dispatchEvent('classroom.close', new Event($classroom));

        return $classroom;
    }

    public function changePicture($id, $data)
    {
        $classroom = $this->getClassroomDao()->get($id);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        $fileIds = ArrayToolkit::column($data, 'id');
        $files = $this->getFileService()->getFilesByIds($fileIds);

        $files = ArrayToolkit::index($files, 'id');
        $fileIds = ArrayToolkit::index($data, 'type');
        $version = ClassroomService::COVER_SIZE_VERSION;
        $fields = [
            'smallPicture' => $files[$fileIds['small']['id']]['uri'] . "?version={$version}",
            'middlePicture' => $files[$fileIds['middle']['id']]['uri'] . "?version={$version}",
            'largePicture' => $files[$fileIds['large']['id']]['uri'] . "?version={$version}",
        ];

        $this->deleteNotUsedPictures($classroom);

        return $this->updateClassroom($id, $fields);
    }

    private function deleteNotUsedPictures($classroom)
    {
        $oldPictures = [
            'smallPicture' => $classroom['smallPicture'] ? $classroom['smallPicture'] : null,
            'middlePicture' => $classroom['middlePicture'] ? $classroom['middlePicture'] : null,
            'largePicture' => $classroom['largePicture'] ? $classroom['largePicture'] : null,
        ];

        $self = $this;
        array_map(
            function ($oldPicture) use ($self) {
                if (!empty($oldPicture)) {
                    $self->getFileService()->deleteFileByUri($oldPicture);
                }
            },
            $oldPictures
        );
    }

    public function isCourseInClassroom($courseId, $classroomId)
    {
        $classroomCourse = $this->getClassroomCourseDao()->getByClassroomIdAndCourseId($classroomId, $courseId);

        return empty($classroomCourse) ? false : true;
    }

    protected function setClassroomCourses($classroomId, array $courseIds)
    {
        $courses = $this->findCoursesByClassroomId($classroomId);
        $existCourseIds = ArrayToolkit::column($courses, 'id');
        foreach ($courseIds as $value) {
            if (!(in_array($value, $existCourseIds))) {
                $this->addCourse($classroomId, $value);
            }
        }
    }

    public function deleteClassroomCourses($classroomId, array $courseIds, $real = true)
    {
        $classroom = $this->getClassroom($classroomId);
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        try {
            $this->beginTransaction();
            foreach ($courses as $course) {
                $classroomRef = $this->getClassroomCourse($classroomId, $course['id']);
                if (empty($classroomRef)) {
                    continue;
                }
                // 最早一批班级中的课程是引用，不是复制。处理这种特殊情况
                if (0 != $classroomRef['parentCourseId']) {
                    $this->getCourseSetService()->unlockCourseSet($course['courseSetId'], true);
                }
                if ($real) {
                    $this->getCourseSetService()->deleteCourseSet($course['courseSetId']);
                }
                $this->getClassroomCourseDao()->deleteByClassroomIdAndCourseId($classroomId, $course['id']);
                $infoData = [
                    'classroomId' => $classroom['id'],
                    'title' => $classroom['title'],
                    'courseSetId' => $course['id'],
                    'courseSetTitle' => $course['courseSetTitle'],
                ];
                $this->getLogService()->info(
                    'classroom',
                    'delete_course',
                    "班级《{$classroom['title']}》(#{$classroom['id']})删除了课程《{$course['title']}》(#{$course['id']})",
                    $infoData
                );
                $this->dispatchEvent(
                    'classroom.course.delete',
                    new Event($classroom, ['deleteCourseId' => $course['id']])
                );
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function countMobileFilledMembersByClassroomId($classroomId, $locked = 0)
    {
        return $this->getClassroomMemberDao()->countMobileFilledMembersByClassroomId($classroomId, $locked);
    }

    public function searchMemberCount($conditions)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getClassroomMemberDao()->count($conditions);
    }

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit)
    {
        return $this->getClassroomMemberDao()->searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit);
    }

    public function getClassroomMember($classroomId, $userId)
    {
        $member = $this->getClassroomMemberDao()->getByClassroomIdAndUserId($classroomId, $userId);

        return !$member ? null : $member;
    }

    public function remarkStudent($classroomId, $userId, $remark)
    {
        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member)) {
            $this->createNewException(ClassroomException::NOTFOUND_MEMBER());
        }

        $fields = ['remark' => empty($remark) ? '' : (string)$remark];

        return $this->getClassroomMemberDao()->update($member['id'], $fields);
    }

    public function removeStudent($classroomId, $userId, $info = [])
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member) || !(array_intersect($member['role'], ['student', 'auditor']))) {
            $this->createNewException(ClassroomException::FORBIDDEN_NOT_STUDENT());
        }

        $this->removeStudentsFromClasroomCourses($classroomId, $userId, $info);

        if (1 == count($member['role'])) {
            $this->getClassroomMemberDao()->delete($member['id']);
        } else {
            foreach ($member['role'] as $key => $value) {
                if ('student' == $value) {
                    unset($member['role'][$key]);
                }
            }

            $this->getClassroomMemberDao()->update($member['id'], $member);
        }

        $classroom = $this->updateStudentNumAndAuditorNum($classroomId);

        $this->createOperateRecord($member, 'exit', $info);

        $currentUser = $this->getCurrentUser();
        $message = [
            'classroomId' => $classroom['id'],
            'classroomTitle' => $classroom['title'],
            'userId' => $currentUser['id'],
            'userName' => $currentUser['nickname'],
            'type' => 'remove',
        ];
        $user = $this->getUserService()->getUser($member['userId']);
        $this->getNotificationService()->notify($user['id'], 'classroom-student', $message);

        $infoData = [
            'classroomId' => $classroom['id'],
            'title' => $classroom['title'],
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
        ];

        if (isset($info['reason_type']) && 'exit' === $info['reason_type']) {
            $this->getLogService()->info(
                'classroom',
                'exit_classroom',
                "学员{$user['nickname']}(#{$user['id']})退出了班级《{$classroom['title']}》(#{$classroom['id']})",
                $infoData
            );
        } else {
            $this->getLogService()->info(
                'classroom',
                'remove_student',
                "班级《{$classroom['title']}》(#{$classroom['id']})，移除学员{$user['nickname']}(#{$user['id']})",
                $infoData
            );
        }

        $this->dispatchEvent(
            'classroom.quit',
            new Event($classroom, ['userId' => $member['userId'], 'member' => $member])
        );
    }

    public function removeStudents($classroomId, $userIds, $info = [])
    {
        if (empty($userIds)) {
            return false;
        }

        foreach ($userIds as $userId) {
            $this->removeStudent($classroomId, $userId, $info);
        }

        return true;
    }

    public function isClassroomStudent($classroomId, $userId)
    {
        $member = $this->getClassroomMember($classroomId, $userId);

        return (empty($member) || !in_array('student', $member['role'])) ? false : true;
    }

    public function isClassroomAssistant($classroomId, $userId)
    {
        $member = $this->getClassroomMember($classroomId, $userId);

        return (empty($member) || !in_array('assistant', $member['role'])) ? false : true;
    }

    public function isClassroomTeacher($classroomId, $userId)
    {
        $member = $this->getClassroomMember($classroomId, $userId);

        return (empty($member) || !in_array('teacher', $member['role'])) ? false : true;
    }

    public function isClassroomHeadTeacher($classroomId, $userId)
    {
        $member = $this->getClassroomMember($classroomId, $userId);

        return (empty($member) || !in_array('headTeacher', $member['role'])) ? false : true;
    }

    public function findTeacherCanManagerClassRoomCourseSet($classroomId)
    {
        $user = $this->getCurrentUser();
        $userId = $user->getId();
        $classRoomCourseSets = $this->getClassroomCourseDao()->findByClassroomId($classroomId);
        $courseSetIds = ArrayToolkit::column($classRoomCourseSets, 'courseSetId');
        if ($user->isSuperAdmin() || $user->isAdmin() || $this->isClassroomHeadTeacher($classroomId, $userId)) {
            return $courseSetIds;
        }

        if ($this->isClassroomTeacher($classroomId, $userId)) {
            $teacherCourseSets = $this->getCourseMemberService()->findTeacherMembersByUserId($userId);
            $courseSetIds = array_intersect(ArrayToolkit::column($teacherCourseSets, 'courseSetId'), $courseSetIds);
        }

        return $courseSetIds;
    }

    // becomeStudent的逻辑条件，写注释
    public function becomeStudent($classroomId, $userId, $info = [])
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        if (!in_array($classroom['status'], ['published', 'closed'])) {
            $this->createNewException(ClassroomException::UNPUBLISHED_CLASSROOM());
        }

        $user = $this->getUserService()->getUser($userId) ?: $this->getUserService()->getUserByUUID($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (!$this->canBecomeClassroomMember($member)) {
            $this->createNewException(ClassroomException::FORBIDDEN_BECOME_STUDENT());
        }

        if (!empty($info['becomeUseMember']) && $this->isPluginInstalled('Vip')) {
            $levelChecked = $this->getVipService()->checkUserVipRight($user['id'], ClassroomVipRightSupplier::CODE, $classroom['id']);

            if ('ok' != $levelChecked) {
                $this->createNewException(ClassroomException::MEMBER_LEVEL_LIMIT());
            }
        }

        if (!empty($info['orderId'])) {
            $order = $this->getOrderService()->getOrder($info['orderId']);

            if (empty($order)) {
                $this->createNewException(OrderException::NOTFOUND_ORDER());
            }
        } else {
            $order = null;
        }

        $deadline = ClassroomToolkit::buildMemberDeadline([
            'expiryMode' => $info['expiryMode'] ?? $classroom['expiryMode'],
            'expiryValue' => $info['expiryDays'] ?? $classroom['expiryValue'],
        ]);

        $refundSetting = $this->getSettingService()->get('refund', []);
        $reason = $this->buildJoinReason($info, $order);
        $note = empty($info['note']) ? '' : $info['note'];
        $fields = [
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => empty($order) ? 0 : $order['id'],
            'joinedChannel' => $reason['reason_type'],
            'role' => ['student'],
            'remark' => empty($info['remark']) ? $note : $info['remark'],
            'deadline' => $deadline,
            'refundDeadline' => empty($refundSetting['maxRefundDays']) ? 0 : strtotime("+ {$refundSetting['maxRefundDays']}days"),
        ];

        if (!empty($member)) {
            $member['orderId'] = $fields['orderId'];
            $member['refundDeadline'] = $fields['refundDeadline'];
            $member['remark'] = $fields['remark'];
            if ('auditor' != $member['role'][0]) {
                $member['role'][] = 'student';
            } else {
                $member['role'] = ['student'];
                $member['deadline'] = $deadline;
                $member['createdTime'] = time();
            }
            $member = $this->getClassroomMemberDao()->update($member['id'], array_merge($member, $this->getMemberHistoryData($member['userId'], $member['classroomId'])));
        } else {
            $member = $this->getClassroomMemberDao()->create(array_merge($fields, $this->getMemberHistoryData($fields['userId'], $fields['classroomId'])));
        }

        $this->createOperateRecord($member, 'join', $reason);

        $params = [
            'orderId' => $fields['orderId'],
            'note' => $fields['remark'],
        ];
        $this->joinClassroomCourses($classroom['id'], $user['id'], $params);

        $fields = [
            'studentNum' => $this->getClassroomStudentCount($classroomId),
            'auditorNum' => $this->getClassroomAuditorCount($classroomId),
        ];

        /*if ($order) {
            $income = $this->getOrderService()->sumOrderPriceByTarget('classroom', $classroomId);
            $fields['income'] = empty($income) ? 0 : $income;
        }*/

        $this->getClassroomDao()->update($classroomId, $fields);
        $this->dispatchEvent(
            'classroom.join',
            new Event($classroom, ['userId' => $member['userId'], 'member' => $member])
        );

        return $member;
    }

    protected function getMemberHistoryData($userId, $classroomId)
    {
        $recordCount = $this->getMemberOperationService()->countRecords([
            'user_id' => $userId,
            'target_type' => 'classroom',
            'target_id' => $classroomId,
            'operate_type' => 'join',
        ]);
        if (empty($recordCount)) {
            return [];
        }

        $classroom = $this->getClassroomDao()->get($classroomId);
        if (empty($classroom)) {
            return [];
        }

        $courseIds = $this->getClassroomCourseDao()->search(['classroomId' => $classroomId], [], 0, PHP_INT_MAX, ['courseId']);
        $courseIds = array_column($courseIds, 'courseId');
        if (empty($courseIds)) {
            return [];
        }

        $learnedNum = $this->getTaskResultService()->countTaskResults(
            ['courseIds' => $courseIds, 'userId' => $userId, 'status' => 'finish']
        );
        $learnedCompulsoryTaskNum = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdAndCourseIds($userId, $courseIds);
        $courseMemberConditions = ['courseIds' => $courseIds, 'userId' => $userId];
        $lastLearnTaskResult = $this->getTaskResultService()->searchTaskResults($courseMemberConditions, ['updatedTime' => 'DESC'], 0, 1, ['updatedTime']);
        $lastFinishedTaskResult = $this->getTaskResultService()->searchTaskResults($courseMemberConditions, ['finishedTime' => 'DESC'], 0, 1, ['finishedTime']);

        $classroomTaskNums = $this->getClassroomCourseDao()->countTaskNumByClassroomIds([$classroomId]);
        $classroomTaskNums = empty($classroomTaskNums) ? [] : $classroomTaskNums[0];

        $courseThreadNum = $this->getCourseThreadService()->countThreads(['courseIds' => $courseIds, 'userId' => $userId, 'type' => 'discussion']);
        $threadNum = $this->getThreadService()->searchThreadCount(['targetType' => 'classroom', 'targetId' => $classroomId, 'userId' => $userId, 'type' => 'discussion']);

        $courseQuestionNum = $this->getCourseThreadService()->countThreads(['courseIds' => $courseIds, 'userId' => $userId, 'type' => 'question']);
        $questionNum = $this->getThreadService()->searchThreadCount(['targetType' => 'classroom', 'targetId' => $classroomId, 'userId' => $userId, 'type' => 'question']);

        return [
            'noteNum' => $this->getCourseNoteService()->countCourseNotes($courseMemberConditions),
            'questionNum' => $courseQuestionNum + $questionNum,
            'threadNum' => $courseThreadNum + $threadNum,
            'isFinished' => $classroomTaskNums['compulsoryTaskNum'] - $learnedCompulsoryTaskNum ? 0 : 1,
            'finishedTime' => empty($lastFinishedTaskResult) ? 0 : $lastFinishedTaskResult[0]['finishedTime'],
            'learnedNum' => $learnedNum,
            'learnedCompulsoryTaskNum' => $learnedCompulsoryTaskNum,
            'learnedElectiveTaskNum' => $learnedNum - $learnedCompulsoryTaskNum ? $learnedNum - $learnedCompulsoryTaskNum : 0,
            'lastLearnTime' => empty($lastLearnTaskResult) ? 0 : $lastLearnTaskResult[0]['updatedTime'],
        ];
    }

    private function buildJoinReason($info, $order)
    {
        if (ArrayToolkit::requireds($info, ['reason', 'reason_type'])) {
            return ArrayToolkit::parts($info, ['reason', 'reason_type']);
        }

        $orderId = empty($order) ? 0 : $order['id'];

        return $this->getMemberOperationService()->getJoinReasonByOrderId($orderId);
    }

    public function becomeStudentWithOrder($classroomId, $userId, $params = [])
    {
        if (!ArrayToolkit::requireds($params, ['price', 'remark'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->tryManageClassroom($classroomId);

        $classroom = $this->getClassroom($classroomId);

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($userId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }

        $isStudent = $this->isClassroomStudent($classroom['id'], $user['id']);
        if ($isStudent) {
            $this->createNewException(ClassroomException::DUPLICATE_JOIN());
        }

        try {
            $this->beginTransaction();

            if ($params['price'] > 0) {
                //支付完成后会自动加入课程
                $product = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
                $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($product['id'], $classroom['id']);

                $order = $this->createOrder($goodsSpecs['id'], $user['id'], $params, 'outside');
            } else {
                $info = [
                    'orderId' => 0,
                    'note' => $params['remark'],
                ];
                $this->becomeStudent($classroom['id'], $user['id'], $info);
                $order = ['id' => 0];
            }

            $member = $this->getClassroomMember($classroom['id'], $user['id']);

            $currentUser = $this->getCurrentUser();
            if (!empty($params['isNotify'])) {
                $message = [
                    'classroomId' => $classroom['id'],
                    'classroomTitle' => $classroom['title'],
                    'userId' => $currentUser['id'],
                    'userName' => $currentUser['nickname'],
                    'type' => 'create',
                ];
                $this->getNotificationService()->notify($member['userId'], 'classroom-student', $message);
            }

            $infoData = [
                'classroomId' => $classroom['id'],
                'title' => $classroom['title'],
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'remark' => $params['remark'],
            ];

            $this->getLogService()->info(
                'classroom',
                'add_student',
                "班级《{$classroom['title']}》(#{$classroom['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$params['remark']}",
                $infoData
            );
            $this->commit();

            return [$classroom, $member, $order];
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function updateClassroomCourses($classroomId, $activeCourseIds)
    {
        $this->tryManageClassroom($classroomId);

        try {
            $this->beginTransaction();

            $courses = $this->findActiveCoursesByClassroomId($classroomId);
            $courses = ArrayToolkit::index($courses, 'id');
            $existCourseIds = ArrayToolkit::column($courses, 'id');

            $diff = array_diff($existCourseIds, $activeCourseIds);
            $classroom = $this->getClassroom($classroomId);
            if (!empty($diff)) {
                foreach ($diff as $courseId) {
                    $this->getCourseService()->unlockCourse($courseId);
                    $this->getCourseService()->closeCourse($courseId); //, 'classroom'

                    $this->getClassroomCourseDao()->deleteByClassroomIdAndCourseId($classroomId, $courseId);
                    $this->getCourseMemberService()->deleteMemberByCourseIdAndRole($courseId, 'student');

                    $course = $this->getCourseService()->getCourse($courseId);
                    $this->getClassroomDao()->wave([$classroomId], ['noteNum' => "-{$course['noteNum']}"]);
                    $this->getLogService()->info(
                        'classroom',
                        'delete_course',
                        "班级《{$classroom['title']}》(#{$classroom['id']})删除了课程《{$course['title']}》(#{$course['id']})"
                    );
                }
            }

            $this->refreshCoursesSeq($classroomId, $activeCourseIds);

            $this->commit();

            $this->dispatchEvent(
                'classroom.course.update',
                new Event($classroom, ['courseIds' => $activeCourseIds, 'existCourseIds' => $existCourseIds])
            );
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function findClassroomsByCoursesIds($courseIds)
    {
        return $this->getClassroomCourseDao()->findByCoursesIds($courseIds);
    }

    public function findClassroomsByCourseSetIds(array $courseSetIds)
    {
        return $this->getClassroomCourseDao()->findByCourseSetIds($courseSetIds);
    }

    public function findClassroomCourseByCourseSetIds($courseSetIds)
    {
        return $this->getClassroomCourseDao()->findByCourseSetIds($courseSetIds);
    }

    private function refreshCoursesSeq($classroomId, $courseIds)
    {
        $seq = 1;

        foreach ($courseIds as $key => $courseId) {
            $classroomCourse = $this->getClassroomCourse($classroomId, $courseId);
            $this->getClassroomCourseDao()->update($classroomCourse['id'], ['seq' => $seq]);
            ++$seq;
        }
    }

    public function getClassroomCourse($classroomId, $courseId)
    {
        return $this->getClassroomCourseDao()->getByClassroomIdAndCourseId($classroomId, $courseId);
    }

    public function findCoursesByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $sortedCourses = [];

        foreach ($classroomCourses as $key => $classroomCourse) {
            $sortedCourses[$key] = $courses[$classroomCourse['courseId']];
            if (empty($sortedCourses[$key]['drainage'])) {
                $sortedCourses[$key]['drainage'] = ['enabled' => 0, 'image' => '', 'text' => ''];
            }
        }

        unset($courses);

        return $sortedCourses;
    }

    public function getClassroomStudentCount($classroomId)
    {
        return $this->getClassroomMemberDao()->countStudents($classroomId);
    }

    public function getClassroomAuditorCount($classroomId)
    {
        return $this->getClassroomMemberDao()->countAuditors($classroomId);
    }

    public function addHeadTeacher($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if ($classroom['headTeacherId']) {
            if ($userId == $classroom['headTeacherId']) {
                return;
            }

            $headTeacherMember = $this->getClassroomMember($classroomId, $classroom['headTeacherId']);

            if (1 == count($headTeacherMember['role'])) {
                $this->getClassroomMemberDao()->deleteByClassroomIdAndUserId($classroomId, $classroom['headTeacherId']);
            } else {
                foreach ($headTeacherMember['role'] as $key => $value) {
                    if ('headTeacher' == $value) {
                        unset($headTeacherMember['role'][$key]);
                    }
                }

                $this->getClassroomMemberDao()->update($headTeacherMember['id'], $headTeacherMember);
            }
        }

        if (!empty($userId)) {
            $this->updateClassroom($classroomId, ['headTeacherId' => $userId]);

            $member = $this->getClassroomMember($classroomId, $userId);

            if ($member) {
                if ('auditor' == $member['role'][0]) {
                    $member['role'][0] = 'headTeacher';
                } else {
                    $member['role'][] = 'headTeacher';
                }

                $this->getClassroomMemberDao()->update($member['id'], $member);
            } else {
                $fields = [
                    'classroomId' => $classroomId,
                    'userId' => $userId,
                    'orderId' => 0,
                    'role' => ['headTeacher'],
                    'remark' => '',
                    'createdTime' => time(),
                ];
                $this->getClassroomMemberDao()->create($fields);
            }

            $this->dispatchEvent('classMaster.become', new Event($member));
        }
    }

    public function updateAssistants($classroomId, $userIds)
    {
        $assistantIds = $this->findAssistants($classroomId);

        $this->addAssistants($classroomId, $userIds, $assistantIds);
        $this->deleteAssistants($classroomId, $userIds, $assistantIds);

        $fields = ['assistantIds' => $userIds];
        $this->getClassroomDao()->update($classroomId, $fields);
    }

    protected function addAssistants($classroomId, $userIds, $existAssistanstIds)
    {
        $addAssistantIds = array_diff($userIds, $existAssistanstIds);

        if (empty($addAssistantIds)) {
            return null;
        }

        $addMembers = $this->findMembersByClassroomIdAndUserIds($classroomId, $addAssistantIds);

        foreach ($addAssistantIds as $userId) {
            $existMember = empty($addMembers[$userId]) ? [] : $addMembers[$userId];

            if ($existMember && in_array('student', $existMember['role'])) {
                $fields = [
                    'role' => $existMember['role'],
                ];
                $fields['role'][] = 'assistant';
                $this->getClassroomMemberDao()->update($addMembers[$userId]['id'], $fields);
            } else {
                $this->createNewException(ClassroomException::FORBIDDEN_NOT_STUDENT());
            }
        }
    }

    protected function deleteAssistants($classroomId, $userIds, $existAssistanstIds)
    {
        $deleteAssistantIds = array_diff($existAssistanstIds, $userIds);

        if (empty($deleteAssistantIds)) {
            return null;
        }

        $deleteMembers = $this->findMembersByClassroomIdAndUserIds($classroomId, $deleteAssistantIds);

        foreach ($deleteAssistantIds as $userId) {
            if (!in_array('assistant', $deleteMembers[$userId]['role'])) {
                continue;
            }

            $fields = [
                'role' => $deleteMembers[$userId]['role'],
            ];

            if (count($fields['role']) > 1) {
                $key = array_search('assistant', $fields['role']);
                array_splice($fields['role'], $key, 1);

                $this->getClassroomMemberDao()->update($deleteMembers[$userId]['id'], $fields);
            } else {
                $this->getClassroomMemberDao()->delete($deleteMembers[$userId]['id']);
            }
        }
    }

    public function becomeAuditor($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        if ('published' != $classroom['status']) {
            $this->createNewException(ClassroomException::UNPUBLISHED_CLASSROOM());
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($userId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (!$this->canBecomeClassroomMember($member)) {
            $this->createNewException(ClassroomException::FORBIDDEN_BECOME_AUDITOR());
        }

        $fields = [
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => 0,
            'role' => ['auditor'],
            'remark' => '',
            'createdTime' => time(),
        ];

        $member = $this->getClassroomMemberDao()->create($fields);
        $data = [
            'reason' => 'site.join_by_auditor',
            'reason_type' => 'auditor_join',
        ];
        $this->createOperateRecord($member, 'join', $data);

        $classroom = $this->updateStudentNumAndAuditorNum($classroomId);
        $this->dispatchEvent(
            'classroom.auditor_join',
            new Event($classroom, ['userId' => $member['userId']])
        );

        return $member;
    }

    public function becomeAssistant($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($userId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }

        $fields = [
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => 0,
            'role' => ['assistant'],
            'remark' => '',
            'createdTime' => time(),
        ];

        $member = $this->getClassroomMemberDao()->create($fields);
        $data = [
            'reason' => 'site.join_by_assistant',
            'reason_type' => 'assistant_join',
        ];
        $this->createOperateRecord($member, 'join', $data);

        $this->dispatchEvent(
            'classroom.become_assistant',
            new Event($classroom, ['userId' => $member['userId']])
        );

        return $member;
    }

    public function becomeTeacher($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        if (!empty($userId)) {
            $user = $this->getUserService()->getUser($userId);

            if (empty($user)) {
                $user = $this->getUserService()->getUserByUUID($userId);
                if(empty($user)) {
                    $this->createNewException(UserException::NOTFOUND_USER());
                }
            }
        } else {
            $user = $this->getCurrentUser();
            if (!in_array('ROLE_SUPER_ADMIN', $user['roles']) && !in_array('ROLE_ADMIN', $user['roles'])) {
                $this->createNewException(UserException::PERMISSION_DENIED());
            }
        }

        $fields = [
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => 0,
            'role' => ['teacher'],
            'remark' => '',
            'createdTime' => TimeMachine::time(),
        ];

        $member = $this->getClassroomMemberDao()->create($fields);

        $this->dispatchEvent(
            'classroom.become_teacher',
            new Event($classroom, ['userId' => $member['userId']])
        );

        return $member;
    }

    public function isClassroomAuditor($classroomId, $studentId)
    {
        $member = $this->getClassroomMember($classroomId, $studentId);

        if ($member) {
            if (in_array('auditor', $member['role'])) {
                return true;
            }
        }

        return false;
    }

    protected function _prepareClassroomConditions($conditions)
    {
        $intList = ['buyable', 'showable'];
        foreach ($intList as $key) {
            if (isset($conditions[$key])) {
                $conditions[$key] = (int)$conditions[$key];
            }
        }

        $conditions = array_filter(
            $conditions,
            function ($value) {
                if (0 === $value || !empty($value)) {
                    return true;
                } else {
                    return false;
                }
            }
        );

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        if (isset($conditions['categoryId'])) {
            $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $conditions['categoryIds'] = array_merge([$conditions['categoryId']], $childrenIds);
            unset($conditions['categoryId']);
        }

        return $conditions;
    }

    private function canBecomeClassroomMember($member)
    {
        return empty($member) || !in_array('student', $member['role']);
    }

    /**
     * @param  $id
     * @param  $permission
     *
     * @return bool
     */
    public function canManageClassroom($id, $permission = 'admin_classroom_content_manage')
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            $permissions = array_merge([$permission], $this->getMarriedPermissions($permission));
            foreach ($permissions as $singlePermission) {
                if ($user->hasPermission($singlePermission)) {
                    return true;
                }
            }
        }

        $member = $this->getClassroomMember($id, $user['id']);

        if (empty($member)) {
            return false;
        }

        if (in_array('headTeacher', $member['role'])) {
            return true;
        }

        return false;
    }

    public function tryManageClassroom($id, $actionPermission = 'admin_v2_classroom_content_manage')
    {
        if (!$this->canManageClassroom($id, $actionPermission)) {
            $this->createNewException(ClassroomException::FORBIDDEN_MANAGE_CLASSROOM());
        }
    }

    public function canTakeClassroom($id, $includeAuditor = false)
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $member = $this->getClassroomMember($id, $user['id']);

        if (empty($member)) {
            return false;
        }

        if (array_intersect($member['role'], ['student', 'assistant', 'teacher', 'headTeacher'])) {
            return true;
        }

        if ($includeAuditor && in_array('auditor', $member['role'])) {
            return true;
        }

        return false;
    }

    public function tryTakeClassroom($id, $includeAuditor = false)
    {
        if (!$this->canTakeClassroom($id, $includeAuditor)) {
            $this->createNewException(ClassroomException::FORBIDDEN_TAKE_CLASSROOM());
        }
    }

    public function canHandleClassroom($id)
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $member = $this->getClassroomMember($id, $user['id']);

        if (empty($member)) {
            return false;
        }

        if (array_intersect($member['role'], ['assistant', 'teacher', 'headTeacher'])) {
            return true;
        }

        return false;
    }

    public function tryHandleClassroom($id)
    {
        if (!$this->canHandleClassroom($id)) {
            $this->createNewException(ClassroomException::FORBIDDEN_HANDLE_CLASSROOM());
        }
    }

    public function canLookClassroom($id)
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin() && $classroom['showable']) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $member = $this->getClassroomMember($id, $user['id']);

        if (empty($member) && $classroom['showable']) {
            return true;
        }

        if ($member) {
            return true;
        }

        return false;
    }

    public function tryLookClassroom($id)
    {
        if (!$this->canLookClassroom($id)) {
            $this->createNewException(ClassroomException::FORBIDDEN_LOOK_CLASSROOM());
        }
    }

    public function canJoinClassroom($id)
    {
        $classroom = $this->getClassroom($id);
        $chain = $this->biz['classroom.join_chain'];

        if (empty($chain)) {
            $this->createNewException(ClassroomException::CHAIN_NOT_REGISTERED());
        }

        return $chain->process($classroom);
    }

    public function canLearnClassroom($id)
    {
        $classroom = $this->getClassroom($id);
        $chain = $this->biz['classroom.learn_chain'];

        if (empty($chain)) {
            $this->createNewException(ClassroomException::CHAIN_NOT_REGISTERED());
        }

        return $chain->process($classroom);
    }

    public function canCreateThreadEvent($resource)
    {
        $classroomId = $resource['targetId'];
        $user = $this->getCurrentUser();
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            return false;
        }

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $member = $this->getClassroomMember($classroomId, $user['id']);

        if (empty($member)) {
            return false;
        }

        return array_intersect($member['role'], ['teacher', 'headTeacher', 'assistant']);
    }

    private function removeStudentsFromClasroomCourses($classroomId, $userId, $info)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);

        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');

        $reason = [
            'reason' => !empty($info['reason']) ? $info['reason'] : 'course.member.operation.reason.classroom_exit',
            'reason_type' => !empty($info['reason_type']) ? $info['reason_type'] : 'classroom_exit',
        ];
        foreach ($courseIds as $key => $courseId) {
            $count = 0;
            $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $userId);
            if (empty($courseMember) || 'student' !== $courseMember['role']) {
                continue;
            }

            $this->getCourseMemberService()->removeStudent($courseId, $userId, $reason);
        }
    }

    protected function isHeadTeacher($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if ($classroom['headTeacherId'] == $userId) {
            return true;
        }

        return false;
    }

    public function findClassroomStudents($classroomId, $start, $limit)
    {
        return $this->getClassroomMemberDao()->findByClassroomIdAndRole($classroomId, 'student', $start, $limit);
    }

    public function findClassroomMembersByRole($classroomId, $role, $start, $limit)
    {
        $members = $this->getClassroomMemberDao()->findByClassroomIdAndRole($classroomId, $role, $start, $limit);

        return !$members ? [] : ArrayToolkit::index($members, 'userId');
    }

    public function findMembersByClassroomIdAndUserIds($classroomId, $userIds)
    {
        $members = $this->getClassroomMemberDao()->findByClassroomIdAndUserIds($classroomId, $userIds);

        return !$members ? [] : ArrayToolkit::index($members, 'userId');
    }

    public function lockStudent($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member)) {
            return;
        }

        if (!in_array('student', $member['role'])) {
            $this->createNewException(ClassroomException::FORBIDDEN_NOT_STUDENT());
        }

        if ($member['locked']) {
            return;
        }

        $this->getClassroomMemberDao()->update($member['id'], ['locked' => 1]);
    }

    public function unlockStudent($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member)) {
            return;
        }

        if (!in_array('student', $member['role'])) {
            $this->createNewException(ClassroomException::FORBIDDEN_NOT_STUDENT());
        }

        if (empty($member['locked'])) {
            return;
        }

        $this->getClassroomMemberDao()->update($member['id'], ['locked' => 0]);
    }

    public function recommendClassroom($id, $number)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_classroom_set_recommend') && !$user->hasPermission('admin_v2_classroom_set_recommend')) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        if (!is_numeric($number)) {
            $this->createNewException(ClassroomException::RECOMMEND_REQUIRED_NUMERIC());
        }

        $classroom = $this->getClassroomDao()->update(
            $id,
            [
                'recommended' => 1,
                'recommendedSeq' => (int)$number,
                'recommendedTime' => time(),
            ]
        );
        $this->getClassroomGoodsMediator()->onRecommended($classroom);

        return $classroom;
    }

    public function cancelRecommendClassroom($id)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_classroom_cancel_recommend') && !$user->hasPermission('admin_v2_classroom_set_recommend')) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $classroom = $this->getClassroomDao()->update(
            $id,
            [
                'recommended' => 0,
                'recommendedTime' => 0,
                'recommendedSeq' => 100,
            ]
        );

        $this->getClassroomGoodsMediator()->onCancelRecommended($classroom);

        return $classroom;
    }

    public function tryAdminClassroom($classroomId)
    {
        $classroom = $this->getClassroomDao()->get($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        $user = $this->getCurrentUser();

        if (empty($user->id)) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        if (0 == count(array_intersect($user['roles'], ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']))) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        return $classroom;
    }

    public function getClassroomMembersByCourseId($courseId, $userId)
    {
        $classroomIds = $this->findClassroomIdsByCourseId($courseId);
        $members = $this->findMembersByUserIdAndClassroomIds($userId, $classroomIds);

        return $members;
    }

    public function findUserJoinedClassroomIds($userId)
    {
        return $this->getClassroomMemberDao()->findByUserId($userId);
    }

    public function updateMember($id, $member)
    {
        return $this->getClassroomMemberDao()->update($id, $member);
    }

    public function updateLearndNumByClassroomIdAndUserId($classroomId, $userId)
    {
        $classroomCourses = $this->findCoursesByClassroomId($classroomId);

        $courseIds = ArrayToolkit::column($classroomCourses, 'id');

        $conditions = [];
        $conditions['courseIds'] = $courseIds;
        $conditions['userId'] = $userId;
        $conditions = [
            'userId' => $userId,
            'courseIds' => $courseIds,
            'status' => 'finish',
        ];
        $userLearnCount = $this->getTaskResultService()->countTaskResults($conditions);
        $classroomMember = $this->getClassroomMember($classroomId, $userId);
        $coursesMembers = ArrayToolkit::index($this->getCourseMemberService()->findCoursesByStudentIdAndCourseIds($userId, $courseIds), 'courseId');
        $fields = [
            'lastLearnTime' => time(),
            'learnedNum' => $userLearnCount,
            'learnedCompulsoryTaskNum' => array_sum(ArrayToolkit::column($coursesMembers, 'learnedCompulsoryTaskNum')),
            'learnedElectiveTaskNum' => array_sum(ArrayToolkit::column($coursesMembers, 'learnedElectiveTaskNum')),
        ];

        return $this->updateMember($classroomMember['id'], $fields);
    }

    public function updateClassroomMemberFinishedStatus($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);
        if (empty($classroom)) {
            return;
        }
        $classroomMember = $this->getClassroomMember($classroomId, $userId);

        if (empty($classroomMember)) {
            return;
        }

        $courses = $this->findCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($courses, 'id');
        $coursesMembers = ArrayToolkit::index($this->getCourseMemberService()->findCoursesByStudentIdAndCourseIds($userId, $courseIds), 'courseId');
        $finished = '1';
        foreach ($courses as $course) {
            if (empty($coursesMembers[$course['id']]) || !$coursesMembers[$course['id']]['isLearned']) {
                $finished = '0';
                break;
            }
        }

        return $this->updateMember($classroomMember['id'], [
            'isFinished' => $finished,
            'finishedTime' => $finished ? max(ArrayToolkit::column($coursesMembers, 'finishedTime')) : 0,
            'learnedCompulsoryTaskNum' => array_sum(ArrayToolkit::column($coursesMembers, 'learnedCompulsoryTaskNum')),
            'learnedElectiveTaskNum' => array_sum(ArrayToolkit::column($coursesMembers, 'learnedElectiveTaskNum')),
        ]);
    }

    public function updateClassroomMembersFinishedStatus($classroomId)
    {
        $classroom = $this->getClassroom($classroomId);
        if (empty($classroom)) {
            return;
        }
        $classroomMembersCount = $this->searchMemberCount(['classroomId' => $classroomId, 'role' => '%student%']);
        if (empty($classroomMembersCount)) {
            return;
        }
        $classroomMembers = $this->findClassroomStudents($classroomId, 0, $classroomMembersCount);

        $courses = $this->findCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($courses, 'id');

        foreach ($classroomMembers as $classroomMember) {
            $coursesMembers = ArrayToolkit::index($this->getCourseMemberService()->findCoursesByStudentIdAndCourseIds($classroomMember['userId'], $courseIds), 'courseId');
            $finished = '1';
            foreach ($courses as $course) {
                if (empty($coursesMembers[$course['id']]) || !$coursesMembers[$course['id']]['isLearned']) {
                    $finished = '0';
                    break;
                }
            }
            $finishedTimes = ArrayToolkit::column($coursesMembers, 'finishedTime');
            $finishedTime = count($finishedTimes) > 0 ? max($finishedTimes) : 0;
            $this->updateMember($classroomMember['id'], [
                'isFinished' => $finished,
                'finishedTime' => $finished ? $finishedTime : 0,
                'learnedCompulsoryTaskNum' => array_sum(ArrayToolkit::column($coursesMembers, 'learnedCompulsoryTaskNum')),
                'learnedElectiveTaskNum' => array_sum(ArrayToolkit::column($coursesMembers, 'learnedElectiveTaskNum')),
            ]);
        }
    }

    public function countCoursesByClassroomId($classroomId)
    {
        return $this->getClassroomCourseDao()->count(
            [
                'classroomId' => $classroomId,
                'disabled' => 0,
            ]
        );
    }

    public function countCourseTasksByClassroomId($classroomId)
    {
        return $this->getClassroomCourseDao()->countCourseTasksByClassroomId($classroomId);
    }

    /**
     * @param $userId
     * @param $classroomId
     *
     * @return array|array[]
     *                       1. 获取班级内课程
     *                       2. 通过班级内课程找到对应的原课程
     *                       3. 筛选出学员拥有的课程（学员member列表）
     *                       4. 查找对应的订单
     */
    public function findUserPaidCoursesInClassroom($userId, $classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $parentCourseIds = ArrayToolkit::column($courses, 'parentId');

        $coursesMember = $this->getCourseMemberService()->findCoursesByStudentIdAndCourseIds($userId, $parentCourseIds);

        $paidCourseIds = ArrayToolkit::column($coursesMember, 'courseId');
        $paidCourses = $this->getCourseService()->findCoursesByIds($paidCourseIds);

        $orderIds = ArrayToolkit::column($coursesMember, 'orderId');

        if (!$orderIds) {
            return [[], []];
        }

        $conditions = [
            'order_ids' => $orderIds,
            'target_type' => 'course',
            'statuses' => ['success', 'finished'],
        ];

        $orderItems = $this->getOrderService()->searchOrderItems($conditions, [], 0, PHP_INT_MAX);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');

        return [$paidCourses, $orderItems];
    }

    /**
     * @param $classroomId
     *
     * @throws UnableJoinException
     *
     * @todo 商品剥离，免费加入，商品凭证
     */
    public function tryFreeJoin($classroomId)
    {
        $access = $this->canJoinClassroom($classroomId);
        if (AccessorInterface::SUCCESS != $access['code']) {
            throw new UnableJoinException($access['msg'], $access['code']);
        }

        $classroom = $this->getClassroom($classroomId);

        if (0 == $classroom['price'] && $classroom['buyable']) {
            $this->becomeStudent($classroom['id'], $this->getCurrentUser()->getId(), ['note' => 'site.join_by_free']);
        }

        $this->dispatch('classroom.try_free_join', $classroom);
    }

    private function updateStudentNumAndAuditorNum($classroomId)
    {
        $fields = [
            'studentNum' => $this->getClassroomStudentCount($classroomId),
            'auditorNum' => $this->getClassroomAuditorCount($classroomId),
        ];

        return $this->getClassroomDao()->update($classroomId, $fields);
    }

    private function addCourse($id, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $courses = $this->getClassroomCourseDao()->search(['classroomId' => $id], ['seq' => 'desc'], 0, 1);
        $maxSeqCourse = empty($courses) ? [] : $courses[0];
        $seq = empty($maxSeqCourse) ? 1 : $maxSeqCourse['seq'] + 1;

        $classroomCourse = [
            'classroomId' => $id,
            'courseId' => $courseId,
            'courseSetId' => $course['courseSetId'],
            'parentCourseId' => $course['parentId'],
            'seq' => $seq,
        ];

        $classroomCourse = $this->getClassroomCourseDao()->create($classroomCourse);
        $this->dispatchEvent('classroom.put_course', $classroomCourse);
    }

    protected function _prepareConditions($conditions)
    {
        if (isset($conditions['role'])) {
            $conditions['role'] = "%{$conditions['role']}%";
        }

        if (isset($conditions['roles'])) {
            foreach ($conditions['roles'] as $key => $role) {
                $conditions['roles'][$key] = $role;
            }
        }

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        if (isset($conditions['categoryId'])) {
            $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $conditions['categoryIds'] = array_merge([$conditions['categoryId']], $childrenIds);
            unset($conditions['categoryId']);
        }

        if (!empty($conditions['nameLike'])) {
            $users = $this->getUserService()->searchUsers(['nickname' => "%{$conditions['nameLike']}%"], [], 0, PHP_INT_MAX, ['id']);
            $conditions['userIds'] = empty($users) ? [-1] : array_column($users, 'id');
        }

        return $conditions;
    }

    protected function joinClassroomCourses($classroomId, $userId, $params)
    {
        $classroomMember = $this->getClassroomMember($classroomId, $userId);

        $courses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($courses, 'courseId');

        $userCourses = $this->getCourseMemberService()->findCoursesByStudentIdAndCourseIds($userId, $courseIds);
        $userCourses = ArrayToolkit::index($userCourses, 'courseId');

        foreach ($courseIds as $key => $courseId) {
            $courseMember = empty($userCourses[$courseId]) ? [] : $userCourses[$courseId];

            if ($courseMember) {
                continue;
            }

            $info = [
                'orderId' => empty($params['orderId']) ? 0 : $params['orderId'],
                'orderNote' => empty($params['note']) ? '' : $params['note'],
                'joinedChannel' => $classroomMember['joinedChannel'],
                'deadline' => $classroomMember['deadline'],
            ];
            $this->getCourseMemberService()->createMemberByClassroomJoined($courseId, $userId, $classroomId, $info);
        }
    }

    protected function createOrder($goodsSpecsId, $userId, $params, $source)
    {
        $classroomProduct = $this->getOrderFacadeService()->getOrderProduct('classroom', ['targetId' => $goodsSpecsId]);

        $params = [
            'created_reason' => $params['remark'],
            'source' => $source,
            'create_extra' => $params,
        ];

        return $this->getOrderFacadeService()->createSpecialOrder($classroomProduct, $userId, $params);
    }

    protected function createOperateRecord($member, $operateType, $reason)
    {
        $currentUser = $this->getCurrentUser();
        $classroom = $this->getClassroom($member['classroomId']);

        $data['member'] = $member;
        $record = [
            'title' => $classroom['title'],
            'user_id' => $member['userId'],
            'member_id' => $member['id'],
            'target_id' => $member['classroomId'],
            'target_type' => 'classroom',
            'operate_type' => $operateType,
            'operate_time' => time(),
            'operator_id' => $currentUser['id'],
            'data' => $data,
            'order_id' => $member['orderId'],
        ];
        $record = array_merge($record, ArrayToolkit::parts($reason, ['reason', 'reason_type']));

        return $this->getMemberOperationService()->createRecord($record);
    }

    public function findMembersByMemberIds($ids)
    {
        $this->getClassroomMemberDao()->findMembersByMemberIds($ids);
    }

    public function refreshClassroomHotSeq()
    {
        return $this->getClassroomDao()->refreshHotSeq();
    }

    protected function getOrderBys($order)
    {
        if (is_array($order)) {
            return $order;
        }

        $typeOrderByMap = [
            'hitNum' => ['hitNum' => 'DESC'],
            'rating' => ['rating' => 'DESC'],
            'studentNum' => ['studentNum' => 'DESC'],
            'recommendedSeq' => ['recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC', 'createdTime' => 'DESC'],
            'hotSeq' => ['hotSeq' => 'DESC', 'studentNum' => 'DESC', 'id' => 'DESC'],
        ];
        if (isset($typeOrderByMap[$order])) {
            return $typeOrderByMap[$order];
        } else {
            return ['createdTime' => 'DESC'];
        }
    }

    public function isMemberNonExpired($classroom, $member)
    {
        if (empty($classroom) || empty($member)) {
            throw $this->createServiceException('classroom, member参数不能为空');
        }

        $vipNonExpired = true;
        if ('vip_join' == $member['joinedChannel']) {
            // 会员加入的情况下
            $vipNonExpired = $this->isVipMemberNonExpired($classroom, $member);
        }

        if (0 == $member['deadline']) {
            return $vipNonExpired;
        }

        if ($member['deadline'] > time()) {
            return $vipNonExpired;
        }

        return !$vipNonExpired;
    }

    public function searchMembersSignStatistics($classroomId, array $conditions, array $orderBy, $start, $limit)
    {
        if (!isset($conditions['classroomId'])) {
            $conditions['classroomId'] = $classroomId;
        }
        $conditions = $this->_prepareConditions($conditions);

        return $this->getClassroomMemberDao()->searchSignStatisticsByClassroomId($classroomId, $conditions, $orderBy, $start, $limit);
    }

    public function appendHasCertificate(array $classrooms)
    {
        $conditions = [
            'targetType' => 'classroom',
            'targetIds' => ArrayToolkit::column($classrooms, 'id'),
            'status' => 'published',
        ];

        $certificates = ArrayToolkit::index($this->getCertificateService()->search($conditions, [], 0, PHP_INT_MAX, ['targetId']), 'targetId');
        foreach ($classrooms as &$classroom) {
            $classroom['hasCertificate'] = !empty($certificates[$classroom['id']]);
        }

        return $classrooms;
    }

    public function hasCertificate($classroomId)
    {
        $conditions = [
            'targetType' => 'classroom',
            'targetId' => $classroomId,
            'status' => 'published',
        ];

        return !empty($this->getCertificateService()->count($conditions));
    }

    public function calClassroomsTaskNums(array $classrooms, $withMemberInfo = false)
    {
        if (empty($classrooms)) {
            return [];
        }

        foreach ($classrooms as &$classroom) {
            if ($withMemberInfo) {
                $classroom['finishedMemberCount'] = $this->getClassroomMemberDao()->count(['classroomId' => $classroom['id'], 'isFinished' => 1]);
            }
        }

        return array_column($classrooms, null, 'id');
    }

    public function updateClassroomMembersNoteAndThreadNums($classroomId)
    {
        $classroom = $this->getClassroom($classroomId);
        if (empty($classroom)) {
            return;
        }

        $classroomMembersCount = $this->searchMemberCount(['classroomId' => $classroomId]);
        if (empty($classroomMembersCount)) {
            return;
        }

        $classroomMembers = $this->searchMembers(['classroomId' => $classroomId], [], 0, $classroomMembersCount, ['id', 'userId']);
        $classroomCourses = $this->findCoursesByClassroomId($classroomId);
        $classroomCourseIds = array_column($classroomCourses, 'courseId');

        foreach ($classroomMembers as $member) {
            $this->getClassroomMemberDao()->update($member['id'], [
                'noteNum' => empty($classroomCourseIds) ? 0 : $this->getCourseNoteService()->countCourseNotes(['courseIds' => $classroomCourseIds, 'userId' => $member['userId']]),
                'threadNum' => $this->getClassroomMemberThreadNum($classroomId, $member['userId'], $classroomCourseIds, 'discussion'),
                'questionNum' => $this->getClassroomMemberThreadNum($classroomId, $member['userId'], $classroomCourseIds, 'question'),
            ]);
        }
    }

    public function updateMemberFieldsByClassroomIdAndUserId($classroomId, $userId, array $fields)
    {
        if (empty($fields)) {
            return;
        }

        $classroomMember = $this->getClassroomMemberDao()->getByClassroomIdAndUserId($classroomId, $userId);
        if (empty($classroomMember)) {
            return;
        }

        $classroomCourses = $this->getClassroomCourseDao()->findByClassroomId($classroomId);
        $classroomCourseIds = array_column($classroomCourses, 'courseId');

        $updateFields = [];
        foreach ($fields as $field) {
            if ('noteNum' === $field) {
                $updateFields['noteNum'] = empty($classroomCourseIds) ? 0 : $this->getCourseNoteService()->countCourseNotes(['courseIds' => $classroomCourseIds, 'userId' => $userId]);
            } elseif ('threadNum' === $field) {
                $updateFields['threadNum'] = $this->getClassroomMemberThreadNum($classroomId, $userId, $classroomCourseIds, 'discussion');
            } elseif ('questionNum' === $field) {
                $updateFields['questionNum'] = $this->getClassroomMemberThreadNum($classroomId, $userId, $classroomCourseIds, 'question');
            }
        }

        if (!empty($updateFields)) {
            $this->getClassroomMemberDao()->update($classroomMember['id'], $updateFields);
        }
    }

    protected function getClassroomMemberThreadNum($classroomId, $userId, $classroomCourseIds, $threadType = 'discussion')
    {
        $courseThreadNum = empty($classroomCourseIds) ? 0 : $this->getCourseThreadService()->countThreads(['courseIds' => $classroomCourseIds, 'userId' => $userId, 'type' => $threadType]);
        $threadNum = $this->getThreadService()->searchThreadCount(['targetType' => 'classroom', 'targetId' => $classroomId, 'userId' => $userId, 'type' => $threadType]);

        return $courseThreadNum + $threadNum;
    }

    /**
     * 会员到期后、会员被取消后、课程会员等级被提高均为过期
     *
     * @param  $classroom
     * @param  $member
     *
     * @return bool 会员加入的学员是否已到期
     */
    protected function isVipMemberNonExpired($classroom, $member)
    {
        if (!$this->isPluginInstalled('Vip')) {
            return false;
        }

        $status = $this->getVipService()->checkUserVipRight($member['userId'], ClassroomVipRightSupplier::CODE, $classroom['id']);

        return 'ok' === $status;
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return FileService
     */
    public function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return ClassroomDao
     */
    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:ClassroomDao');
    }

    /**
     * @return ClassroomMemberDao
     */
    protected function getClassroomMemberDao()
    {
        return $this->createDao('Classroom:ClassroomMemberDao');
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
     * @return ClassroomCourseDao
     */
    protected function getClassroomCourseDao()
    {
        return $this->createDao('Classroom:ClassroomCourseDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }

    protected function getMemberOperationService()
    {
        return $this->biz->service('MemberOperation:MemberOperationService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return ClassroomGoodsMediator
     */
    protected function getClassroomGoodsMediator()
    {
        return $this->biz['goods.mediator.classroom'];
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->createService('Certificate:CertificateService');
    }

    /**
     * @return GoodsEntityFactory
     */
    protected function getGoodsEntityFactory()
    {
        $biz = $this->biz;

        return $biz['goods.entity.factory'];
    }

    /**
     * @return CourseThreadService
     */
    protected function getCourseThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ProductMallGoodsRelationService
     */

    protected function getProductMallGoodsRelationService()
    {
        return $this->createService('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
