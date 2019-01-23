<?php

namespace Biz\Classroom\Service\Impl;

use Biz\Accessor\AccessorInterface;
use Biz\BaseService;
use Biz\Classroom\ClassroomException;
use Biz\Common\CommonException;
use Biz\Course\Dao\CourseNoteDao;
use Biz\Exception\UnableJoinException;
use Biz\Order\OrderException;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\LogService;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\User\UserException;
use Codeages\Biz\Order\Service\OrderService;
use Biz\User\Service\StatusService;
use Biz\Content\Service\FileService;
use Biz\Taxonomy\Service\TagService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use AppBundle\Common\ClassroomToolkit;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Biz\Classroom\Dao\ClassroomCourseDao;
use Biz\Classroom\Dao\ClassroomMemberDao;
use Biz\Taxonomy\Service\CategoryService;
use VipPlugin\Biz\Vip\Service\VipService;
use Biz\Classroom\Service\ClassroomService;
use AppBundle\Common\TimeMachine;

class ClassroomServiceImpl extends BaseService implements ClassroomService
{
    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getClassroomMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function findClassroomsByIds(array $ids)
    {
        return ArrayToolkit::index($this->getClassroomDao()->findByIds($ids), 'id');
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);
        if (empty($classroomCourses)) {
            return array();
        }

        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        if (empty($courses)) {
            return array();
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

        $sortedCourses = array();
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
            return array();
        }

        return ArrayToolkit::index($members, 'classroomId');
    }

    public function getClassroom($id)
    {
        return $this->getClassroomDao()->get($id);
    }

    public function searchClassrooms($conditions, $orderBy, $start, $limit)
    {
        $orderBy = $this->getOrderBys($orderBy);
        $conditions = $this->_prepareClassroomConditions($conditions);

        return $this->getClassroomDao()->search($conditions, $orderBy, $start, $limit);
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

    /**
     * @deprecated
     *
     * @param int $courseId
     *
     * @return array
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
            return array();
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
            return array();
        }

        $assistantIds = ArrayToolkit::column($assistants, 'userId');
        $oldAssistantIds = $classroom['assistantIds'] ?: array();

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
            return array();
        }

        $classroom = $this->getClassroom($classroomId);
        $teacherIds = ArrayToolkit::column($teachers, 'userId');
        $oldTeacherIds = $classroom['teacherIds'] ?: array();

        if (!empty($oldTeacherIds)) {
            $orderTeacherIds = array_intersect($oldTeacherIds, $teacherIds);
            $orderTeacherIds = array_merge($orderTeacherIds, array_diff($teacherIds, $oldTeacherIds));
        } else {
            $orderTeacherIds = $teacherIds;
        }

        return $orderTeacherIds;
    }

    public function addClassroom($classroom)
    {
        $title = trim($classroom['title']);

        if (empty($title)) {
            $this->createNewException(ClassroomException::EMPTY_TITLE());
        }

        $classroom = $this->fillOrgId($classroom);
        $userId = $this->getCurrentUser()->getId();
        $classroom['creator'] = $userId;
        $classroom['teacherIds'] = array($userId);
        $classroom['expiryMode'] = 'forever';
        $classroom['expiryValue'] = 0;

        $classroom = $this->getClassroomDao()->create($classroom);
        $this->becomeTeacher($classroom['id'], $userId);

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
                $newCourseIds = array();

                foreach ($courses as $key => $course) {
                    $newCourse = $this->getCourseSetService()->copyCourseSet(
                        $classroomId,
                        $course['courseSetId'],
                        $course['id']
                    );
                    $newCourseIds[] = $newCourse['id'];

                    $infoData = array(
                        'classroomId' => $classroom['id'],
                        'title' => $classroom['title'],
                        'courseSetId' => $newCourse['id'],
                        'courseSetTitle' => $newCourse['title'],
                    );

                    $this->getLogService()->info(
                        'classroom',
                        'add_course',
                        "班级《{$classroom['title']}》(#{$classroom['id']})添加了课程《{$newCourse['title']}》(#{$newCourse['id']})",
                        $infoData
                    );
                }

                $this->setClassroomCourses($classroomId, $newCourseIds);
            }
            $this->refreshCoursesSeq($classroomId, $courseIds);

            $this->dispatchEvent(
                'classroom.course.create',
                new Event($classroom, array('courseIds' => $courseIds))
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
        $tagIds = empty($fields['tagIds']) ? array() : $fields['tagIds'];

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

        $arguments = $fields;
        $arguments['tagIds'] = $tagIds;

        $this->dispatchEvent('classroom.update', new Event(array(
            'userId' => $user['id'],
            'classroom' => $classroom,
            'fields' => $arguments,
        )));

        return $classroom;
    }

    public function updateMembersDeadlineByClassroomId($classroomId, $deadline)
    {
        return $this->getClassroomMemberDao()->updateByClassroomIdAndRole($classroomId, 'student', array(
            'deadline' => $deadline,
        ));
    }

    protected function canUpdateMembersDeadline($classroom, $expiryMode)
    {
        if ($expiryMode == $classroom['expiryMode'] && 'days' != $expiryMode) {
            return true;
        }

        return false;
    }

    protected function canUpdateClassroomExpiryDate($fields, $classroom)
    {
        if (empty($fields['expiryMode']) && empty($fields['expiryValue'])) {
            return true;
        }

        if ('draft' == $classroom['status']) {
            return true;
        }

        if ($fields['expiryMode'] == $classroom['expiryMode']) {
            return true;
        }

        return false;
    }

    protected function filterClassroomFields($fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'rating',
            'ratingNum',
            'categoryId',
            'title',
            'status',
            'about',
            'description',
            'price',
            'vipLevelId',
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
        ));

        if (isset($fields['expiryMode']) && 'date' == $fields['expiryMode']) {
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

        if ('date' == $classroom['expiryMode'] && $classroom['expiryValue'] < time()) {
            return true;
        }

        return false;
    }

    public function updateMemberDeadlineByMemberId($memberId, $deadline)
    {
        $member = $this->getClassroomMemberDao()->update($memberId, $deadline);

        $this->dispatchEvent('classroom.member.deadline.update', new Event(array(
            'userId' => $member['userId'],
            'deadline' => $deadline['deadline'],
            'classroomId' => $member['classroomId'],
        )));

        return $this->getClassroomMemberDao()->update($memberId, $deadline);
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

        $shouldNotifyClassrooms = array();
        $shouldNotifyClassroomMembers = array();

        $currentTime = time();

        foreach ($classrooms as $classroom) {
            $member = $members[$classroom['id']];

            if ($classroom['expiryValue'] > 0 && 0 == $member['deadlineNotified'] && $currentTime < $member['deadline'] && (10 * 24 * 680 * 60 + $currentTime) > $member['deadline']) {
                $shouldNotifyClassrooms[] = $classroom;
                $shouldNotifyClassroomMembers[] = $member;
            }
        }

        return array($shouldNotifyClassrooms, $shouldNotifyClassroomMembers);
    }

    public function batchUpdateOrg($classroomIds, $orgCode)
    {
        if (!is_array($classroomIds)) {
            $classroomIds = array($classroomIds);
        }
        $fields = $this->fillOrgId(array('orgCode' => $orgCode));

        foreach ($classroomIds as $classroomId) {
            $this->getClassroomDao()->update($classroomId, $fields);
        }
    }

    public function waveClassroom($id, $field, $diff)
    {
        $fields = array(
            'hitNum',
            'auditorNum',
            'studentNum',
            'courseNum',
            'lessonNum',
            'threadNum',
            'postNum',
            'noteNum',
        );

        if (!in_array($field, $fields)) {
            $this->createNewException(ClassroomException::FORBIDDEN_WAVE());
        }

        return $this->getClassroomDao()->wave(array($id), array($field => $diff));
    }

    private function deleteAllCoursesInClass($id)
    {
        $courses = $this->findCoursesByClassroomId($id);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $this->deleteClassroomCourses($id, $courseIds);
    }

    public function deleteClassroom($id)
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        if ('draft' != $classroom['status']) {
            $this->createNewException(ClassroomException::FORBIDDEN_DELETE_NOT_DRAFT());
        }

        $this->tryManageClassroom($id, 'admin_classroom_delete');

        $this->deleteAllCoursesInClass($id);
        $this->getClassroomDao()->delete($id);

        $this->dispatchEvent('classroom.delete', $classroom);

        return true;
    }

    /**
     * @todo 能否简化业务逻辑？
     */
    public function updateClassroomTeachers($id)
    {
        $classroom = $this->getClassroom($id);
        $courses = $this->findActiveCoursesByClassroomId($id);

        $oldTeacherIds = $this->findTeachers($id);
        $newTeacherIds = array($classroom['creator']);

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
                if ('auditor' == $addMembers[$userId]['role'][0]) {
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
            if (1 == count($deleteMembers[$userId]['role'])) {
                $this->getClassroomMemberDao()->delete($deleteMembers[$userId]['id']);
            } else {
                foreach ($deleteMembers[$userId]['role'] as $key => $value) {
                    if ('teacher' == $value) {
                        unset($deleteMembers[$userId]['role'][$key]);
                    }
                }

                $this->getClassroomMemberDao()->update($deleteMembers[$userId]['id'], $deleteMembers[$userId]);
            }
        }
    }

    public function publishClassroom($id)
    {
        $this->tryManageClassroom($id, 'admin_classroom_open');

        $this->updateClassroom($id, array('status' => 'published'));
    }

    public function closeClassroom($id)
    {
        $this->tryManageClassroom($id, 'admin_classroom_close');

        $this->updateClassroom($id, array('status' => 'closed'));
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

        $fields = array(
            'smallPicture' => $files[$fileIds['small']['id']]['uri'],
            'middlePicture' => $files[$fileIds['middle']['id']]['uri'],
            'largePicture' => $files[$fileIds['large']['id']]['uri'],
        );

        $this->deleteNotUsedPictures($classroom);

        return $this->updateClassroom($id, $fields);
    }

    private function deleteNotUsedPictures($classroom)
    {
        $oldPictures = array(
            'smallPicture' => $classroom['smallPicture'] ? $classroom['smallPicture'] : null,
            'middlePicture' => $classroom['middlePicture'] ? $classroom['middlePicture'] : null,
            'largePicture' => $classroom['largePicture'] ? $classroom['largePicture'] : null,
        );

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

    public function deleteClassroomCourses($classroomId, array $courseIds)
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

                $this->getClassroomCourseDao()->deleteByClassroomIdAndCourseId($classroomId, $course['id']);

                $infoData = array(
                    'classroomId' => $classroom['id'],
                    'title' => $classroom['title'],
                    'courseSetId' => $course['id'],
                    'courseSetTitle' => $course['courseSetTitle'],
                );

                $this->getLogService()->info(
                    'classroom',
                    'delete_course',
                    "班级《{$classroom['title']}》(#{$classroom['id']})删除了课程《{$course['title']}》(#{$course['id']})",
                    $infoData
                );

                $this->dispatchEvent(
                    'classroom.course.delete',
                    new Event($classroom, array('deleteCourseId' => $course['id']))
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
        $conditions = $this->_prepareClassroomConditions($conditions);

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

        $fields = array('remark' => empty($remark) ? '' : (string) $remark);

        return $this->getClassroomMemberDao()->update($member['id'], $fields);
    }

    public function removeStudent($classroomId, $userId, $info = array())
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member) || !(array_intersect($member['role'], array('student', 'auditor')))) {
            $this->createNewException(ClassroomException::FORBIDDEN_NOT_STUDENT());
        }

        $this->removeStudentsFromClasroomCourses($classroomId, $userId);

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

        $user = $this->getUserService()->getUser($member['userId']);
        $message = array(
            'classroomId' => $classroom['id'],
            'classroomTitle' => $classroom['title'],
            'userId' => $user['id'],
            'userName' => $user['nickname'],
            'type' => 'remove',
        );
        $this->getNotificationService()->notify($user['id'], 'classroom-student', $message);

        $infoData = array(
            'classroomId' => $classroom['id'],
            'title' => $classroom['title'],
            'userId' => $user['id'],
            'nickname' => $user['nickname'],
        );

        $this->getLogService()->info(
            'classroom',
            'remove_student',
            "班级《{$classroom['title']}》(#{$classroom['id']})，移除学员{$user['nickname']}(#{$user['id']})",
            $infoData
        );

        $this->dispatchEvent(
            'classroom.quit',
            new Event($classroom, array('userId' => $member['userId'], 'member' => $member))
        );
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

    // becomeStudent的逻辑条件，写注释
    public function becomeStudent($classroomId, $userId, $info = array())
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            $this->createNewException(ClassroomException::NOTFOUND_CLASSROOM());
        }

        if (!in_array($classroom['status'], array('published', 'closed'))) {
            $this->createNewException(ClassroomException::UNPUBLISHED_CLASSROOM());
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (!$this->canBecomeClassroomMember($member)) {
            $this->createNewException(ClassroomException::FORBIDDEN_BECOME_STUDENT());
        }

        $userMember = array();

        if (!empty($info['becomeUseMember'])) {
            $levelChecked = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroom['vipLevelId']);

            if ('ok' != $levelChecked) {
                $this->createNewException(ClassroomException::MEMBER_LEVEL_LIMIT());
            }

            $userMember = $this->getVipService()->getMemberByUserId($user['id']);
        }

        if (!empty($info['orderId'])) {
            $order = $this->getOrderService()->getOrder($info['orderId']);

            if (empty($order)) {
                $this->createNewException(OrderException::NOTFOUND_ORDER());
            }
        } else {
            $order = null;
        }

        $deadline = ClassroomToolkit::buildMemberDeadline(array(
            'expiryMode' => $classroom['expiryMode'],
            'expiryValue' => $classroom['expiryValue'],
        ));

        $refundSetting = $this->getSettingService()->get('refund', array());
        $fields = array(
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => empty($order) ? 0 : $order['id'],
            'levelId' => empty($info['becomeUseMember']) ? 0 : $userMember['levelId'],
            'role' => array('student'),
            'remark' => empty($info['note']) ? '' : $info['note'],
            'deadline' => $deadline,
            'refundDeadline' => empty($refundSetting['maxRefundDays']) ? 0 : strtotime("+ {$refundSetting['maxRefundDays']}days"),
        );

        if (!empty($member)) {
            $member['orderId'] = $fields['orderId'];
            $member['refundDeadline'] = $fields['refundDeadline'];
            if ('auditor' != $member['role'][0]) {
                $member['role'][] = 'student';
                $member['levelId'] = $fields['levelId'];
                $member['remark'] = $fields['remark'];
            } else {
                $member['role'] = array('student');
                $member['deadline'] = $deadline;
            }

            $member = $this->getClassroomMemberDao()->update($member['id'], $member);
        } else {
            $member = $this->getClassroomMemberDao()->create($fields);
        }

        $reason = $this->buildJoinReason($info, $order);
        $this->createOperateRecord($member, 'join', $reason);

        $params = array(
            'orderId' => $fields['orderId'],
            'note' => $fields['remark'],
        );
        $this->joinClassroomCourses($classroom['id'], $user['id'], $params);

        $fields = array(
            'studentNum' => $this->getClassroomStudentCount($classroomId),
            'auditorNum' => $this->getClassroomAuditorCount($classroomId),
        );

        /*if ($order) {
            $income = $this->getOrderService()->sumOrderPriceByTarget('classroom', $classroomId);
            $fields['income'] = empty($income) ? 0 : $income;
        }*/

        $this->getClassroomDao()->update($classroomId, $fields);
        $this->dispatchEvent(
            'classroom.join',
            new Event($classroom, array('userId' => $member['userId'], 'member' => $member))
        );

        return $member;
    }

    private function buildJoinReason($info, $order)
    {
        if (ArrayToolkit::requireds($info, array('reason', 'reason_type'))) {
            return ArrayToolkit::parts($info, array('reason', 'reason_type'));
        }

        $orderId = empty($order) ? 0 : $order['id'];

        return $this->getMemberOperationService()->getJoinReasonByOrderId($orderId);
    }

    public function becomeStudentWithOrder($classroomId, $userId, $params = array())
    {
        if (!ArrayToolkit::requireds($params, array('price', 'remark'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->tryManageClassroom($classroomId);

        $classroom = $this->getClassroom($classroomId);

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $isStudent = $this->isClassroomStudent($classroom['id'], $user['id']);
        if ($isStudent) {
            $this->createNewException(ClassroomException::DUPLICATE_JOIN());
        }

        try {
            $this->beginTransaction();

            if ($params['price'] > 0) {
                //支付完成后会自动加入课程
                $order = $this->createOrder($classroom['id'], $user['id'], $params, 'outside');
            } else {
                $info = array(
                    'orderId' => 0,
                    'note' => $params['remark'],
                );
                $this->becomeStudent($classroom['id'], $user['id'], $info);
                $order = array('id' => 0);
            }

            $member = $this->getClassroomMember($classroom['id'], $user['id']);

            $currentUser = $this->getCurrentUser();
            if (!empty($params['isNotify'])) {
                $message = array(
                    'classroomId' => $classroom['id'],
                    'classroomTitle' => $classroom['title'],
                    'userId' => $currentUser['id'],
                    'userName' => $currentUser['nickname'],
                    'type' => 'create',
                );
                $this->getNotificationService()->notify($member['userId'], 'classroom-student', $message);
            }

            $infoData = array(
                'classroomId' => $classroom['id'],
                'title' => $classroom['title'],
                'userId' => $user['id'],
                'nickname' => $user['nickname'],
                'remark' => $params['remark'],
            );

            $this->getLogService()->info(
                'classroom',
                'add_student',
                "班级《{$classroom['title']}》(#{$classroom['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$params['remark']}",
                $infoData
            );
            $this->commit();

            return array($classroom, $member, $order);
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
                    $this->getClassroomDao()->wave(array($classroomId), array('noteNum' => "-{$course['noteNum']}"));
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
                new Event($classroom, array('courseIds' => $activeCourseIds))
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
            $this->getClassroomCourseDao()->update($classroomCourse['id'], array('seq' => $seq));
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
        $sortedCourses = array();

        foreach ($classroomCourses as $key => $classroomCourse) {
            $sortedCourses[$key] = $courses[$classroomCourse['courseId']];
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
            $this->updateClassroom($classroomId, array('headTeacherId' => $userId));

            $member = $this->getClassroomMember($classroomId, $userId);

            if ($member) {
                if ('auditor' == $member['role'][0]) {
                    $member['role'][0] = 'headTeacher';
                } else {
                    $member['role'][] = 'headTeacher';
                }

                $this->getClassroomMemberDao()->update($member['id'], $member);
            } else {
                $fields = array(
                    'classroomId' => $classroomId,
                    'userId' => $userId,
                    'orderId' => 0,
                    'levelId' => 0,
                    'role' => array('headTeacher'),
                    'remark' => '',
                    'createdTime' => time(),
                );
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

        $fields = array('assistantIds' => $userIds);
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
            $existMember = empty($addMembers[$userId]) ? array() : $addMembers[$userId];

            if ($existMember && in_array('student', $existMember['role'])) {
                $fields = array(
                    'role' => $existMember['role'],
                );
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

            $fields = array(
                'role' => $deleteMembers[$userId]['role'],
            );

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
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (!$this->canBecomeClassroomMember($member)) {
            $this->createNewException(ClassroomException::FORBIDDEN_BECOME_AUDITOR());
        }

        $fields = array(
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => 0,
            'levelId' => 0,
            'role' => array('auditor'),
            'remark' => '',
            'createdTime' => time(),
        );

        $member = $this->getClassroomMemberDao()->create($fields);
        $data = array(
            'reason' => 'site.join_by_auditor',
            'reason_type' => 'auditor_join',
        );
        $this->createOperateRecord($member, 'join', $data);

        $classroom = $this->updateStudentNumAndAuditorNum($classroomId);
        $this->dispatchEvent(
            'classroom.auditor_join',
            new Event($classroom, array('userId' => $member['userId']))
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
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $fields = array(
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => 0,
            'levelId' => 0,
            'role' => array('assistant'),
            'remark' => '',
            'createdTime' => time(),
        );

        $member = $this->getClassroomMemberDao()->create($fields);
        $data = array(
            'reason' => 'site.join_by_assistant',
            'reason_type' => 'assistant_join',
        );
        $this->createOperateRecord($member, 'join', $data);

        $this->dispatchEvent(
            'classroom.become_assistant',
            new Event($classroom, array('userId' => $member['userId']))
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
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        } else {
            $user = $this->getCurrentUser();
            if (!in_array('ROLE_SUPER_ADMIN', $user['roles']) && !in_array('ROLE_ADMIN', $user['roles'])) {
                $this->createNewException(UserException::PERMISSION_DENIED());
            }
        }

        $fields = array(
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => 0,
            'levelId' => 0,
            'role' => array('teacher'),
            'remark' => '',
            'createdTime' => TimeMachine::time(),
        );

        $member = $this->getClassroomMemberDao()->create($fields);

        $this->dispatchEvent(
            'classroom.become_teacher',
            new Event($classroom, array('userId' => $member['userId']))
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
        $intList = array('buyable', 'showable');
        foreach ($intList as $key) {
            if (isset($conditions[$key])) {
                $conditions[$key] = (int) $conditions[$key];
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
            $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
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

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->hasPermission($permission)) {
            return true;
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

    public function tryManageClassroom($id, $actionPermission = null)
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

        if (array_intersect($member['role'], array('student', 'assistant', 'teacher', 'headTeacher'))) {
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

        if (array_intersect($member['role'], array('assistant', 'teacher', 'headTeacher'))) {
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

        return array_intersect($member['role'], array('teacher', 'headTeacher', 'assistant'));
    }

    private function removeStudentsFromClasroomCourses($classroomId, $userId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);

        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');

        $reason = array(
            'reason' => 'course.member.operation.reason.classroom_exit',
            'reason_type' => 'classroom_exit',
        );
        foreach ($courseIds as $key => $courseId) {
            $count = 0;
            $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $userId);
            if ('student' != $courseMember['role']) {
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

        return !$members ? array() : ArrayToolkit::index($members, 'userId');
    }

    public function findMembersByClassroomIdAndUserIds($classroomId, $userIds)
    {
        $members = $this->getClassroomMemberDao()->findByClassroomIdAndUserIds($classroomId, $userIds);

        return !$members ? array() : ArrayToolkit::index($members, 'userId');
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

        $this->getClassroomMemberDao()->update($member['id'], array('locked' => 1));
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

        $this->getClassroomMemberDao()->update($member['id'], array('locked' => 0));
    }

    public function recommendClassroom($id, $number)
    {
        $this->tryAdminClassroom($id);

        if (!is_numeric($number)) {
            $this->createNewException(ClassroomException::RECOMMEND_REQUIRED_NUMERIC());
        }

        $classroom = $this->getClassroomDao()->update(
            $id,
            array(
                'recommended' => 1,
                'recommendedSeq' => (int) $number,
                'recommendedTime' => time(),
            )
        );

        return $classroom;
    }

    public function cancelRecommendClassroom($id)
    {
        $this->tryAdminClassroom($id);

        $classroom = $this->getClassroomDao()->update(
            $id,
            array(
                'recommended' => 0,
                'recommendedTime' => 0,
                'recommendedSeq' => 100,
            )
        );

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

        if (0 == count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN')))) {
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

        $conditions = array();
        $conditions['courseIds'] = $courseIds;
        $conditions['userId'] = $userId;
        $conditions = array(
            'userId' => $userId,
            'courseIds' => $courseIds,
            'status' => 'finish',
        );
        $userLearnCount = $this->getTaskResultService()->countTaskResults($conditions);

        $fields['lastLearnTime'] = time();
        $fields['learnedNum'] = $userLearnCount;

        $classroomMember = $this->getClassroomMember($classroomId, $userId);

        return $this->updateMember($classroomMember['id'], $fields);
    }

    public function countCoursesByClassroomId($classroomId)
    {
        return $this->getClassroomCourseDao()->count(
            array(
                'classroomId' => $classroomId,
                'disabled' => 0,
            )
        );
    }

    public function countCourseTasksByClassroomId($classroomId)
    {
        return $this->getClassroomCourseDao()->countCourseTasksByClassroomId($classroomId);
    }

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
            return array(array(), array());
        }

        $conditions = array(
            'order_ids' => $orderIds,
            'target_type' => 'course',
            'statuses' => array('success', 'finished'),
        );

        $orderItems = $this->getOrderService()->searchOrderItems($conditions, array(), 0, PHP_INT_MAX);
        $orderItems = ArrayToolkit::index($orderItems, 'order_id');

        return array($paidCourses, $orderItems);
    }

    public function tryFreeJoin($classroomId)
    {
        $access = $this->canJoinClassroom($classroomId);
        if (AccessorInterface::SUCCESS != $access['code']) {
            throw new UnableJoinException($access['msg'], $access['code']);
        }

        $classroom = $this->getClassroom($classroomId);

        if (0 == $classroom['price'] && $classroom['buyable']) {
            $this->becomeStudent($classroom['id'], $this->getCurrentUser()->getId(), array('note' => 'site.join_by_free'));
        }

        $this->dispatch('classroom.try_free_join', $classroom);
    }

    private function updateStudentNumAndAuditorNum($classroomId)
    {
        $fields = array(
            'studentNum' => $this->getClassroomStudentCount($classroomId),
            'auditorNum' => $this->getClassroomAuditorCount($classroomId),
        );

        return $this->getClassroomDao()->update($classroomId, $fields);
    }

    private function addCourse($id, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $classroomCourse = array(
            'classroomId' => $id,
            'courseId' => $courseId,
            'courseSetId' => $course['courseSetId'],
            'parentCourseId' => $course['parentId'],
        );

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
            $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            unset($conditions['categoryId']);
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
            $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $userId);
            $courseMember = empty($userCourses[$courseId]) ? array() : $userCourses[$courseId];

            if ($courseMember) {
                continue;
            }

            $info = array(
                'orderId' => empty($params['orderId']) ? 0 : $params['orderId'],
                'orderNote' => empty($params['note']) ? '' : $params['note'],
                'levelId' => empty($classroomMember['levelId']) ? 0 : $classroomMember['levelId'],
                'deadline' => $classroomMember['deadline'],
            );
            $this->getCourseMemberService()->createMemberByClassroomJoined($courseId, $userId, $classroomId, $info);
        }
    }

    protected function createOrder($classroomId, $userId, $params, $source)
    {
        $classroomProduct = $this->getOrderFacadeService()->getOrderProduct('classroom', array('targetId' => $classroomId));

        $params = array(
            'created_reason' => $params['remark'],
            'source' => $source,
            'create_extra' => $params,
        );

        return $this->getOrderFacadeService()->createSpecialOrder($classroomProduct, $userId, $params);
    }

    protected function createOperateRecord($member, $operateType, $reason)
    {
        $currentUser = $this->getCurrentUser();
        $classroom = $this->getClassroom($member['classroomId']);

        $data['member'] = $member;
        $record = array(
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
        );
        $record = array_merge($record, ArrayToolkit::parts($reason, array('reason', 'reason_type')));

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

        $typeOrderByMap = array(
            'hitNum' => array('hitNum' => 'DESC'),
            'rating' => array('rating' => 'DESC'),
            'studentNum' => array('studentNum' => 'DESC'),
            'recommendedSeq' => array('recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC'),
            'hotSeq' => array('hotSeq' => 'DESC', 'studentNum' => 'DESC', 'id' => 'DESC'),
        );
        if (isset($typeOrderByMap[$order])) {
            return $typeOrderByMap[$order];
        } else {
            return array('createdTime' => 'DESC');
        }
    }

    public function isMemberNonExpired($classroom, $member)
    {
        if (empty($classroom) || empty($member)) {
            throw $this->createServiceException('classroom, member参数不能为空');
        }

        $vipNonExpired = true;
        if (!empty($member['levelId'])) {
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

    /**
     * 会员到期后、会员被取消后、课程会员等级被提高均为过期
     *
     * @param  $course
     * @param  $member
     *
     * @return bool 会员加入的学员是否已到期
     */
    protected function isVipMemberNonExpired($classroom, $member)
    {
        $vipApp = $this->getAppService()->getAppByCode('vip');

        if (empty($vipApp)) {
            return false;
        }

        $status = $this->getVipService()->checkUserInMemberLevel($member['userId'], $classroom['vipLevelId']);

        return 'ok' === $status;
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
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
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
     * @return CourseNoteDao
     */
    protected function getNoteDao()
    {
        return $this->createDao('Course:CourseNoteDao');
    }

    /**
     * @return StatusService
     */
    protected function getStatusService()
    {
        return $this->createService('User:StatusService');
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
}
