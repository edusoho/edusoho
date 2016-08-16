<?php

namespace Classroom\Service\Classroom\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Classroom\Service\Classroom\ClassroomService;

class ClassroomServiceImpl extends BaseService implements ClassroomService
{
    public function getClassroom($id)
    {
        $classroom = $this->getClassroomDao()->getClassroom($id);

        return $classroom;
    }

    public function searchClassrooms($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareClassroomConditions($conditions);
        return $this->getClassroomDao()->searchClassrooms($conditions, $orderBy, $start, $limit);
    }

    public function searchClassroomsCount($conditions)
    {
        $conditions = $this->_prepareClassroomConditions($conditions);
        $count      = $this->getClassroomDao()->searchClassroomsCount($conditions);

        return $count;
    }

    public function findClassroomIdsByCourseId($courseId)
    {
        $results = $this->getClassroomCourseDao()->findClassroomIdsByCourseId($courseId);
        return ArrayToolkit::column($results, 'classroomId');
    }

    public function findClassroomsByCourseId($courseId)
    {
        $classroomIds = $this->findClassroomIdsByCourseId($courseId);
        return $this->findClassroomsByIds($classroomIds);
    }

    public function findClassroomByCourseId($courseId)
    {
        return $this->getClassroomCourseDao()->findClassroomByCourseId($courseId);
    }

    public function findAssistants($classroomId)
    {
        $classroom  = $this->getClassroom($classroomId);
        $assistants = $this->getClassroomMemberDao()->findAssistants($classroomId);

        if (!$assistants) {
            return array();
        }

        $assistantIds    = ArrayToolkit::column($assistants, 'userId');
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
        $teachers = $this->getClassroomMemberDao()->findTeachers($classroomId);

        if (!$teachers) {
            return array();
        }

        $classroom     = $this->getClassroom($classroomId);
        $teacherIds    = ArrayToolkit::column($teachers, 'userId');
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
            throw $this->createServiceException('班级名称不能为空！');
        }

        $classroom = $this->fillOrgId($classroom);

        $classroom['createdTime'] = time();
        $classroom                = $this->getClassroomDao()->addClassroom($classroom);
        $this->dispatchEvent("classroom.create", $classroom);
        $this->getLogService()->info('classroom', 'create', "创建班级《{$classroom['title']}》(#{$classroom['id']})");

        return $classroom;
    }

    public function addCoursesToClassroom($classroomId, $courseIds)
    {
        $this->tryManageClassroom($classroomId);
        $this->getClassroomDao()->getConnection()->beginTransaction();
        try {
            $allExistingCourses = $this->findCoursesByClassroomId($classroomId);

            $existCourseIds = ArrayToolkit::column($allExistingCourses, 'parentId');

            $diff      = array_diff($courseIds, $existCourseIds);
            $classroom = $this->getClassroom($classroomId);
            if (!empty($diff)) {
                $courses      = $this->getCourseService()->findCoursesByIds($diff);
                $newCourseIds = array();

                foreach ($courses as $key => $course) {
                    $newCourse      = $this->getCourseCopyService()->copy($course, true);
                    $newCourseIds[] = $newCourse['id'];
                    $this->getLogService()->info('classroom', 'add_course', "班级《{$classroom['title']}》(#{$classroom['id']})添加了课程《{$newCourse['title']}》(#{$newCourse['id']})");
                }

                $this->setClassroomCourses($classroomId, $newCourseIds);
            }

            $courses    = $this->findActiveCoursesByClassroomId($classroomId);
            $coursesNum = count($courses);

            $lessonNum = 0;

            foreach ($courses as $key => $course) {
                $lessonNum += $course['lessonNum'];
            }

            $this->updateClassroom($classroomId, array("courseNum" => $coursesNum, "lessonNum" => $lessonNum));

            $this->updateClassroomTeachers($classroomId);

            $this->refreshCoursesSeq($classroomId, $courseIds);

            $this->getClassroomDao()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getClassroomDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function findClassroomByTitle($title)
    {
        return $this->getClassroomDao()->findClassroomByTitle($title);
    }

    public function findClassroomsByLikeTitle($title)
    {
        return $this->getClassroomDao()->findClassroomsByLikeTitle($title);
    }

    /**
     * 要过滤要更新的字段
     */
    public function updateClassroom($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('rating', 'ratingNum', 'categoryId', 'title', 'status', 'about', 'description', 'price', 'vipLevelId', 'smallPicture', 'middlePicture', 'largePicture', 'headTeacherId', 'teacherIds', 'assistantIds', 'hitNum', 'auditorNum', 'studentNum', 'courseNum', 'lessonNum', 'threadNum', 'postNum', 'income', 'createdTime', 'private', 'service', 'maxRate', 'buyable', 'showable', 'conversationId', 'orgCode', 'orgId'));

        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新失败！');
        }

        $fields    = $this->fillOrgId($fields);
        $classroom = $this->getClassroomDao()->updateClassroom($id, $fields);
        return $classroom;
    }

    public function batchUpdateOrg($classroomIds, $orgCode)
    {
        if (!is_array($classroomIds)) {
            $classroomIds = array($classroomIds);
        }
        $fields = $this->fillOrgId(array('orgCode' => $orgCode));

        foreach ($classroomIds as $classroomId) {
            $user = $this->getClassroomDao()->updateClassroom($classroomId, $fields);
        }
    }

    public function waveClassroom($id, $field, $diff)
    {
        return $this->getClassroomDao()->waveClassroom($id, $field, $diff);
    }

    private function deleteAllCoursesInClass($id)
    {
        $courses   = $this->findCoursesByClassroomId($id);
        $courseIds = ArrayToolkit::column($courses, 'id');

        $this->deleteClassroomCourses($id, $courseIds);
    }

    public function deleteClassroom($id)
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            throw $this->createServiceException('班级不存在，操作失败。');
        }

        if ($classroom['status'] != 'draft') {
            throw $this->createServiceException('只有未发布班级可以删除，操作失败。');
        }

        $this->tryManageClassroom($id);

        $this->deleteAllCoursesInClass($id);
        $this->getClassroomDao()->deleteClassroom($id);
        $this->getLogService()->info('Classroom', 'delete', "班级#{$id}永久删除");

        return true;
    }

    /**
     * @todo 能否简化业务逻辑？
     */
    public function updateClassroomTeachers($id)
    {
        $courses = $this->findActiveCoursesByClassroomId($id);

        $classroom = $this->getClassroom($id);

        $oldTeacherIds = $this->findTeachers($id);
        $newTeacherIds = array();
        $teacherIds    = array();

        foreach ($courses as $key => $value) {
            $teachers      = $this->getCourseService()->findCourseTeachers($value['id']);
            $teacherIds    = ArrayToolkit::column($teachers, 'userId');
            $newTeacherIds = array_merge($newTeacherIds, $teacherIds);
        }

        $newTeacherIds = array_unique($newTeacherIds);
        $ids           = array();

        foreach ($newTeacherIds as $key => $value) {
            $ids[] = $value;
        }

        $newTeacherIds    = $ids;
        $deleteTeacherIds = array_diff($oldTeacherIds, $newTeacherIds);
        $addTeacherIds    = array_diff($newTeacherIds, $oldTeacherIds);
        $addMembers       = $this->findMembersByClassroomIdAndUserIds($id, $addTeacherIds);
        $deleteMembers    = $this->findMembersByClassroomIdAndUserIds($id, $deleteTeacherIds);

        foreach ($addTeacherIds as $userId) {
            if (!empty($addMembers[$userId])) {
                if ($addMembers[$userId]['role'][0] == 'auditor') {
                    $addMembers[$userId]['role'][0] = 'teacher';
                } else {
                    $addMembers[$userId]['role'][] = 'teacher';
                }

                $addMembers[$userId] = MemberSerialize::serialize($addMembers[$userId]);
                $this->getClassroomMemberDao()->updateMember($addMembers[$userId]['id'], $addMembers[$userId]);
            } else {
                $member = $this->becomeTeacher($id, $userId);
            }
        }

        foreach ($deleteTeacherIds as $userId) {
            if (count($deleteMembers[$userId]['role']) == 1) {
                $this->getClassroomMemberDao()->deleteMember($deleteMembers[$userId]['id']);
            } else {
                foreach ($deleteMembers[$userId]['role'] as $key => $value) {
                    if ($value == 'teacher') {
                        unset($deleteMembers[$userId]['role'][$key]);
                    }
                }

                $deleteMembers[$userId] = MemberSerialize::serialize($deleteMembers[$userId]);
                $this->getClassroomMemberDao()->updateMember($deleteMembers[$userId]['id'], $deleteMembers[$userId]);
            }
        }
    }

    public function publishClassroom($id)
    {
        $this->tryManageClassroom($id);

        $this->updateClassroom($id, array("status" => "published"));
    }

    public function closeClassroom($id)
    {
        $this->tryManageClassroom($id);

        $this->updateClassroom($id, array("status" => "closed"));
    }

    public function changePicture($id, $data)
    {
        $classroom = $this->getClassroomDao()->getClassroom($id);

        if (empty($classroom)) {
            throw $this->createServiceException('班级不存在，图标更新失败！');
        }

        $fileIds = ArrayToolkit::column($data, "id");
        $files   = $this->getFileService()->getFilesByIds($fileIds);

        $files   = ArrayToolkit::index($files, "id");
        $fileIds = ArrayToolkit::index($data, "type");

        $fields = array(
            'smallPicture'  => $files[$fileIds["small"]["id"]]["uri"],
            'middlePicture' => $files[$fileIds["middle"]["id"]]["uri"],
            'largePicture'  => $files[$fileIds["large"]["id"]]["uri"]
        );

        $this->deleteNotUsedPictures($classroom);

        $this->getLogService()->info('classroom', 'update_picture', "更新课程《{$classroom['title']}》(#{$classroom['id']})图片", $fields);

        return $this->updateClassroom($id, $fields);
    }

    private function deleteNotUsedPictures($classroom)
    {
        $oldPictures = array(
            'smallPicture'  => $classroom['smallPicture'] ? $classroom['smallPicture'] : null,
            'middlePicture' => $classroom['middlePicture'] ? $classroom['middlePicture'] : null,
            'largePicture'  => $classroom['largePicture'] ? $classroom['largePicture'] : null
        );

        $self = $this;
        array_map(function ($oldPicture) use ($self) {
            if (!empty($oldPicture)) {
                $self->getFileService()->deleteFileByUri($oldPicture);
            }
        }, $oldPictures);
    }

    public function isCourseInClassroom($courseId, $classroomId)
    {
        $classroomCourse = $this->getClassroomCourseDao()->getCourseByClassroomIdAndCourseId($classroomId, $courseId);

        return empty($classroomCourse) ? false : true;
    }

    public function setClassroomCourses($classroomId, array $courseIds)
    {
        $courses        = $this->findCoursesByClassroomId($classroomId);
        $existCourseIds = ArrayToolkit::column($courses, 'id');

        foreach ($courseIds as $key => $value) {
            if (!(in_array($value, $existCourseIds))) {
                $this->addCourse($classroomId, $value);
            }
        }
    }

    public function deleteClassroomCourses($classroomId, array $courseIds)
    {
        foreach ($courseIds as $key => $value) {
            $this->getClassroomCourseDao()->deleteCourseByClassroomIdAndCourseId($classroomId, $value);
        }
    }

    public function findMembersByUserIdAndClassroomIds($userId, array $classroomIds)
    {
        $members = $this->getClassroomMemberDao()->findMembersByUserIdAndClassroomIds($userId, $classroomIds);
        return !$members ? array() : ArrayToolkit::index(MemberSerialize::unserializes($members), 'classroomId');
    }

    public function findMobileVerifiedMemberCountByClassroomId($classroomId, $locked = 0)
    {
        return $this->getClassroomMemberDao()->findMobileVerifiedMemberCountByClassroomId($classroomId, $locked);
    }

    public function findClassroomsByIds(array $ids)
    {
        return ArrayToolkit::index($this->getClassroomDao()->findClassroomsByIds($ids), 'id');
    }

    public function searchMemberCount($conditions)
    {
        $conditions = $this->_prepareClassroomConditions($conditions);

        return $this->getClassroomMemberDao()->searchMemberCount($conditions);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareClassroomConditions($conditions);
        $members    = $this->getClassroomMemberDao()->searchMembers($conditions, $orderBy, $start, $limit);
        return !$members ? array() : MemberSerialize::unserializes($members);
    }

    public function findMemberUserIdsByClassroomId($classroomId)
    {
        return $this->getClassroomMemberDao()->findMemberUserIdsByClassroomId($classroomId);
    }

    public function getClassroomMember($classroomId, $userId)
    {
        $member = $this->getClassroomMemberDao()->getMemberByClassroomIdAndUserId($classroomId, $userId);
        return !$member ? null : MemberSerialize::unserialize($member);
    }

    public function remarkStudent($classroomId, $userId, $remark)
    {
        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member)) {
            throw $this->createServiceException('学员不存在，备注失败!');
        }

        $fields = array('remark' => empty($remark) ? '' : (string) $remark);

        return $this->getClassroomMemberDao()->updateMember($member['id'], $fields);
    }

    public function removeStudent($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createServiceException('班级不存在，操作失败。');
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member) || !(array_intersect($member['role'], array('student', 'auditor')))) {
            throw $this->createServiceException("用户(#{$userId})不是班级(#{$classroomId})的学员，退出班级失败。");
        }

        $this->removeStudentsFromClasroomCourses($classroomId, $userId);

        if (count($member['role']) == 1) {
            $this->getClassroomMemberDao()->deleteMember($member['id']);
        } else {
            foreach ($member['role'] as $key => $value) {
                if ($value == 'student') {
                    unset($member['role'][$key]);
                }
            }

            $member = MemberSerialize::serialize($member);
            $this->getClassroomMemberDao()->updateMember($member['id'], $member);
        }

        $classroom = $this->updateStudentNumAndAuditorNum($classroomId);

        $this->getLogService()->info('classroom', 'remove_student', "班级《{$classroom['title']}》(#{$classroom['id']})，移除学员#{$member['id']}");
        $this->dispatchEvent(
            'classroom.quit',
            new ServiceEvent($classroom, array('userId' => $member['userId'], 'member' => $member))
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
            throw $this->createNotFoundException();
        }

        if (!in_array($classroom['status'], array('published', 'closed'))) {
            throw $this->createServiceException('不能加入未发布班级');
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户(#{$userId})不存在，加入班级失败！");
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (!$this->canBecomeClassroomMember($member)) {
            throw $this->createServiceException("该用户(#{$userId})不能成为该班级学员！");
        }

        $levelChecked = '';

        if (!empty($info['becomeUseMember'])) {
            $levelChecked = $this->getVipService()->checkUserInMemberLevel($user['id'], $classroom['vipLevelId']);

            if ($levelChecked != 'ok') {
                throw $this->createServiceException("用户(#{$userId})不能以会员身份加入班级！");
            }

            $userMember = $this->getVipService()->getMemberByUserId($user['id']);
        }

        if (!empty($info['orderId'])) {
            $order = $this->getOrderService()->getOrder($info['orderId']);

            if (empty($order)) {
                throw $this->createServiceException("订单(#{$info['orderId']})不存在，加入班级失败！");
            }
        } else {
            $order = null;
        }

        $fields = array(
            'classroomId' => $classroomId,
            'userId'      => $userId,
            'orderId'     => empty($order) ? 0 : $order['id'],
            'levelId'     => empty($info['becomeUseMember']) ? 0 : $userMember['levelId'],
            'role'        => '|student|',
            'remark'      => empty($order['note']) ? '' : $order['note'],
            'createdTime' => time()
        );

        if (empty($fields['remark'])) {
            $fields['remark'] = empty($info['note']) ? '' : $info['note'];
        }

        if (!empty($member)) {
            if ($member['role'][0] != 'auditor') {
                $member['role'][]  = 'student';
                $member['orderId'] = $fields['orderId'];
                $member['levelId'] = $fields['levelId'];
                $member['remark']  = $fields['remark'];
            } else {
                $member['role']    = array('student');
                $member['orderId'] = $fields['orderId'];
            }

            $member = MemberSerialize::serialize($member);
            $member = $this->getClassroomMemberDao()->updateMember($member['id'], $member);
        } else {
            $member = $this->getClassroomMemberDao()->addMember($fields);
        }

        $courses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);

        $courseIds = ArrayToolkit::column($courses, "courseId");

        foreach ($courseIds as $key => $courseId) {
            $courseMember = $this->getCourseService()->getCourseMember($courseId, $userId);

            if (empty($courseMember)) {
                $info = array(
                    'orderId'   => empty($order) ? 0 : $order['id'],
                    'orderNote' => empty($order['note']) ? '' : $order['note']
                );
                $this->getCourseService()->createMemberByClassroomJoined($courseId, $userId, $classroomId, $info);
            }
        }

        $fields = array(
            'studentNum' => $this->getClassroomStudentCount($classroomId),
            'auditorNum' => $this->getClassroomAuditorCount($classroomId)
        );

        if ($order) {
            $income           = $this->getOrderService()->sumOrderPriceByTarget('classroom', $classroomId);
            $fields['income'] = empty($income) ? 0 : $income;
        }

        $this->getClassroomDao()->updateClassroom($classroomId, $fields);
        $this->dispatchEvent(
            'classroom.join',
            new ServiceEvent($classroom, array('userId' => $member['userId'], 'member' => $member))
        );

        return $member;
    }

    public function updateClassroomCourses($classroomId, $activeCourseIds)
    {
        $this->tryManageClassroom($classroomId);
        $this->getClassroomDao()->getConnection()->beginTransaction();
        try {
            $courses        = $this->findActiveCoursesByClassroomId($classroomId);
            $courses        = ArrayToolkit::index($courses, 'id');
            $existCourseIds = ArrayToolkit::column($courses, 'id');

            $diff      = array_diff($existCourseIds, $activeCourseIds);
            $classroom = $this->getClassroom($classroomId);
            if (!empty($diff)) {
                foreach ($diff as $courseId) {
                    $this->getCourseService()->updateCourse($courseId, array('locked' => 0));
                    $this->getClassroomCourseDao()->deleteCourseByClassroomIdAndCourseId($classroomId, $courseId);
                    $this->getCourseService()->deleteMemberByCourseIdAndRole($courseId, 'student');
                    $this->getCourseService()->closeCourse($courseId, 'classroom');
                    $course = $this->getCourseService()->getCourse($courseId);
                    $this->getClassroomDao()->waveClassroom($classroomId, 'noteNum', "-{$course['noteNum']}");
                    $this->getLogService()->info('classroom', 'delete_course', "班级《{$classroom['title']}》(#{$classroom['id']})删除了课程《{$course['title']}》(#{$course['id']})");
                }

                $courses    = $this->findActiveCoursesByClassroomId($classroomId);
                $coursesNum = count($courses);

                $lessonNum = 0;

                foreach ($courses as $key => $course) {
                    $lessonNum += $course['lessonNum'];
                }

                $this->updateClassroom($classroomId, array("courseNum" => $coursesNum, "lessonNum" => $lessonNum));

                $this->updateClassroomTeachers($classroomId);
            }

            $this->refreshCoursesSeq($classroomId, $activeCourseIds);

            $this->getClassroomDao()->getConnection()->commit();

            $this->dispatchEvent(
                'classroom.course.delete',
                new ServiceEvent($activeCourseIds, array('classroomId' => $classroomId))
            );
        } catch (\Exception $e) {
            $this->getClassroomDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function findCoursesByCoursesIds($courseIds)
    {
        return $this->getClassroomCourseDao()->findCoursesByCoursesIds($courseIds);
    }

    public function findClassroomsByCoursesIds($courseIds)
    {
        return $this->getClassroomCourseDao()->findClassroomsByCoursesIds($courseIds);
    }

    private function refreshCoursesSeq($classroomId, $courseIds)
    {
        $seq = 1;

        foreach ($courseIds as $key => $courseId) {
            $classroomCourse = $this->findClassroomCourse($classroomId, $courseId);
            $this->getClassroomCourseDao()->update($classroomCourse['id'], array('seq' => $seq));
            $seq++;
        }
    }

    public function findClassroomCourse($classroomId, $courseId)
    {
        return $this->getClassroomCourseDao()->findClassroomCourse($classroomId, $courseId);
    }

    public function findCoursesByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findCoursesByClassroomId($classroomId);
        $courseIds        = ArrayToolkit::column($classroomCourses, "courseId");
        $courses          = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses          = ArrayToolkit::index($courses, "id");
        $sordtedCourses   = array();

        foreach ($classroomCourses as $key => $classroomCourse) {
            $sordtedCourses[$key] = $courses[$classroomCourse["courseId"]];
        }

        return $sordtedCourses;
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);
        $courseIds        = ArrayToolkit::column($classroomCourses, 'courseId');

        if (empty($courseIds)) {
            return array();
        }

        $courses        = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses        = ArrayToolkit::index($courses, 'id');
        $sordtedCourses = array();

        foreach ($classroomCourses as $key => $classroomCourse) {
            $sordtedCourses[$key]                        = $courses[$classroomCourse['courseId']];
            $sordtedCourses[$key]['classroom_course_id'] = $classroomCourse['id'];
        }

        return $sordtedCourses;
    }

    public function getClassroomStudentCount($classroomId)
    {
        return $this->getClassroomMemberDao()->getClassroomStudentCount($classroomId);
    }

    public function getClassroomAuditorCount($classroomId)
    {
        return $this->getClassroomMemberDao()->getClassroomAuditorCount($classroomId);
    }

    public function addHeadTeacher($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if ($classroom['headTeacherId']) {
            $headTeacherMember = $this->getClassroomMember($classroomId, $classroom['headTeacherId']);

            if (count($headTeacherMember['role']) == 1) {
                $this->getClassroomMemberDao()->deleteMemberByClassroomIdAndUserId($classroomId, $classroom['headTeacherId']);
            } else {
                foreach ($headTeacherMember['role'] as $key => $value) {
                    if ($value == 'headTeacher') {
                        unset($headTeacherMember['role'][$key]);
                    }
                }

                $member = MemberSerialize::serialize($headTeacherMember);
                $this->getClassroomMemberDao()->updateMember($member['id'], $member);
            }
        }

        if (!empty($userId)) {
            $this->updateClassroom($classroomId, array('headTeacherId' => $userId));

            $member = $this->getClassroomMember($classroomId, $userId);
            $fields = array(
                'classroomId' => $classroomId,
                'userId'      => $userId,
                'orderId'     => 0,
                'levelId'     => 0,
                'role'        => '|headTeacher|',
                'remark'      => '',
                'createdTime' => time()
            );

            if ($member) {
                if ($member['role'][0] == 'auditor') {
                    $member['role'][0] = 'headTeacher';
                } else {
                    $member['role'][] = 'headTeacher';
                }

                $member = MemberSerialize::serialize($member);
                $this->getClassroomMemberDao()->updateMember($member['id'], $member);
            } else {
                $this->getClassroomMemberDao()->addMember($fields);
            }

            $this->dispatchEvent('classMaster.become', new ServiceEvent($member));
        }
    }

    public function updateAssistants($classroomId, $userIds)
    {
        $assistantIds       = $this->findAssistants($classroomId);
        $deleteAssistantIds = array_diff($assistantIds, $userIds);
        $addAssistantIds    = array_diff($userIds, $assistantIds);
        $addMembers         = $this->findMembersByClassroomIdAndUserIds($classroomId, $addAssistantIds);
        $deleteMembers      = $this->findMembersByClassroomIdAndUserIds($classroomId, $deleteAssistantIds);

        foreach ($addAssistantIds as $userId) {
            if (!empty($addMembers[$userId])) {
                if ($addMembers[$userId]['role'][0] == 'auditor') {
                    $addMembers[$userId]['role'][0] = 'assistant';
                } else {
                    $addMembers[$userId]['role'][] = 'assistant';
                }

                $addMembers[$userId] = MemberSerialize::serialize($addMembers[$userId]);
                $this->getClassroomMemberDao()->updateMember($addMembers[$userId]['id'], $addMembers[$userId]);
            } else {
                $member = $this->becomeAssistant($classroomId, $userId);
            }
        }

        foreach ($deleteAssistantIds as $userId) {
            if (count($deleteMembers[$userId]['role']) == 1) {
                $this->getClassroomMemberDao()->deleteMember($deleteMembers[$userId]['id']);
            } else {
                foreach ($deleteMembers[$userId]['role'] as $key => $value) {
                    if ($value == 'assistant') {
                        unset($deleteMembers[$userId]['role'][$key]);
                    }
                }

                $deleteMembers[$userId] = MemberSerialize::serialize($deleteMembers[$userId]);
                $this->getClassroomMemberDao()->updateMember($deleteMembers[$userId]['id'], $deleteMembers[$userId]);
            }
        }
    }

    public function becomeAuditor($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        if ($classroom['status'] != 'published') {
            throw $this->createServiceException('不能加入未发布班级');
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户(#{$userId})不存在，加入班级失败！");
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (!$this->canBecomeClassroomMember($member)) {
            throw $this->createServiceException("该用户(#{$userId})不能成为该班级的旁听生！");
        }

        $fields = array(
            'classroomId' => $classroomId,
            'userId'      => $userId,
            'orderId'     => 0,
            'levelId'     => 0,
            'role'        => '|auditor|',
            'remark'      => '',
            'createdTime' => time()
        );

        $member = $this->getClassroomMemberDao()->addMember($fields);

        $classroom = $this->updateStudentNumAndAuditorNum($classroomId);
        $this->dispatchEvent(
            'classroom.auditor_join',
            new ServiceEvent($classroom, array('userId' => $member['userId']))
        );

        return $member;
    }

    public function becomeAssistant($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户(#{$userId})不存在，加入班级失败！");
        }

        $fields = array(
            'classroomId' => $classroomId,
            'userId'      => $userId,
            'orderId'     => 0,
            'levelId'     => 0,
            'role'        => '|assistant|',
            'remark'      => '',
            'createdTime' => time()
        );

        $member = $this->getClassroomMemberDao()->addMember($fields);

        $this->dispatchEvent(
            'classroom.become_assistant',
            new ServiceEvent($classroom, array('userId' => $member['userId']))
        );

        return $member;
    }

    public function becomeTeacher($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("用户(#{$userId})不存在，加入班级失败！");
        }

        $fields = array(
            'classroomId' => $classroomId,
            'userId'      => $userId,
            'orderId'     => 0,
            'levelId'     => 0,
            'role'        => '|teacher|',
            'remark'      => '',
            'createdTime' => time()
        );

        $member = $this->getClassroomMemberDao()->addMember($fields);

        $this->dispatchEvent(
            'classroom.become_teacher',
            new ServiceEvent($classroom, array('userId' => $member['userId']))
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

    private function _prepareClassroomConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === 0 || !empty($value)) {
                return true;
            } else {
                return false;
            }
        });

        if (isset($conditions['nickname'])) {
            $user                 = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        if (isset($conditions['categoryId'])) {
            $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            unset($conditions['categoryId']);
        }

        return $conditions;
    }

    private function canBecomeClassroomMember($member)
    {
        return empty($member) || !in_array('student', $member['role']);
    }

    public function canManageClassroom($id)
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

        if (array_intersect($member['role'], array('headTeacher'))) {
            return true;
        }

        return false;
    }

    public function tryManageClassroom($id)
    {
        if (!$this->canManageClassroom($id)) {
            throw $this->createAccessDeniedException('您无权操作！');
        }
    }

    public function canTakeClassroom($id, $isStudentOrAuditor = false)
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isAdmin() && !$isStudentOrAuditor) {
            return true;
        }

        $member = $this->getClassroomMember($id, $user['id']);

        if (empty($member)) {
            return false;
        }

        if ($isStudentOrAuditor) {
            if (array_intersect($member['role'], array('student', 'assistant', 'auditor', 'teacher', 'headTeacher'))) {
                return true;
            }
        } else {
            if (array_intersect($member['role'], array('student', 'assistant', 'teacher', 'headTeacher'))) {
                return true;
            }
        }

        return false;
    }

    public function tryTakeClassroom($id)
    {
        if (!$this->canTakeClassroom($id)) {
            throw $this->createAccessDeniedException('您无权操作！');
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
            throw $this->createAccessDeniedException('您无权操作！');
        }
    }

    public function canLookClassroom($id)
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            return true;
        }

        $member = $this->getClassroomMember($id, $user['id']);

        if ($member) {
            return true;
        }

        if ($classroom['showable'] && !$member['locked']) {
            return true;
        }

        if (!$user->isLogin()) {
            return false;
        }

        return false;
    }

    public function tryLookClassroom($id)
    {
        if (!$this->canLookClassroom($id)) {
            throw $this->createAccessDeniedException('您无权操作！');
        }
    }

    public function canCreateThreadEvent($resource)
    {
        $classroomId = $resource['targetId'];
        $user        = $this->getCurrentUser();
        $classroom   = $this->getClassroom($classroomId);

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

    // @todo 写逻辑条件的注释
    public function exitClassroom($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (!$member) {
            throw $this->createAccessDeniedException('您不是班级学员，无法退出班级！');
        }

        if (!array_intersect($member['role'], array('student', 'auditor'))) {
            throw $this->createAccessDeniedException('教师无法退出班级！');
        }

        $this->removeStudentsFromClasroomCourses($classroomId, $userId);

        if (count($member['role']) == 1) {
            $this->getClassroomMemberDao()->deleteMemberByClassroomIdAndUserId($classroomId, $userId);
        } else {
            foreach ($member['role'] as $key => $value) {
                if ($value == 'student') {
                    unset($member['role'][$key]);
                }
            }

            $member = MemberSerialize::serialize($member);
            $this->getClassroomMemberDao()->updateMember($member['id'], $member);
        }

        $this->updateStudentNumAndAuditorNum($classroomId);

        $this->dispatchEvent(
            'classroom.quit',
            new ServiceEvent($classroom, array('userId' => $userId, 'member' => $member))
        );
    }

    private function removeStudentsFromClasroomCourses($classroomId, $userId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);

        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');

        foreach ($courseIds as $key => $courseId) {
            $count        = 0;
            $courseMember = $this->getCourseService()->getCourseMember($courseId, $userId);

            if ($courseMember && $courseMember['joinedType'] == "course") {
                unset($courseIds[$key]);
                continue;
            }

            $classroomIds = $this->getClassroomCourseDao()->findClassroomIdsByCourseId($courseId);
            $classroomIds = ArrayToolkit::column($classroomIds, "classroomId");

            foreach ($classroomIds as $value) {
                if ($classroomId == $value) {
                    continue;
                }

                $member = $this->getClassroomMember($value, $userId);

                if ($member) {
                    $count = 1;
                    break;
                }
            }

            if ($count == 1) {
                unset($courseIds[$key]);
            }
        }

        foreach ($courseIds as $key => $value) {
            if ($this->getCourseService()->isCourseStudent($value, $userId)) {
                $this->getCourseService()->removeStudent($value, $userId);
            }
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
        $members = $this->getClassroomMemberDao()->findMembersByClassroomIdAndRole($classroomId, 'student', $start, $limit);
        return !$members ? array() : MemberSerialize::unserializes($members);
    }

    public function findClassroomMembersByRole($classroomId, $role, $start, $limit)
    {
        $members = $this->getClassroomMemberDao()->findMembersByClassroomIdAndRole($classroomId, $role, $start, $limit);
        return !$members ? array() : ArrayToolkit::index(MemberSerialize::unserializes($members), 'userId');
    }

    public function findMembersByClassroomIdAndUserIds($classroomId, $userIds)
    {
        $members = $this->getClassroomMemberDao()->findMembersByClassroomIdAndUserIds($classroomId, $userIds);

        return !$members ? array() : ArrayToolkit::index(MemberSerialize::unserializes($members), 'userId');
    }

    public function lockStudent($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException("班级(#{$classroomId})不存在，封锁学员失败。");
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member) || !in_array('student', $member['role'])) {
            throw $this->createServiceException("用户(#{$userId})不是班级(#{$classroomId})的学员，封锁学员失败。");
        }

        if ($member['locked']) {
            return;
        }

        $this->getClassroomMemberDao()->updateMember($member['id'], array('locked' => 1));
    }

    public function unlockStudent($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException("班级(#{$classroomId})不存在，封锁学员失败。");
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member) || !in_array('student', $member['role'])) {
            throw $this->createServiceException("用户(#{$userId})不是该班级(#{$classroomId})的学员，解封学员失败。");
        }

        if (empty($member['locked'])) {
            return;
        }

        $this->getClassroomMemberDao()->updateMember($member['id'], array('locked' => 0));
    }

    public function recommendClassroom($id, $number)
    {
        $this->tryAdminClassroom($id);

        if (!is_numeric($number)) {
            throw $this->createAccessDeniedException('推荐班级序号只能为数字！');
        }

        $classroom = $this->getClassroomDao()->updateClassroom($id, array(
            'recommended'     => 1,
            'recommendedSeq'  => (int) $number,
            'recommendedTime' => time()
        ));

        $this->getLogService()->info('classroom', 'recommend', "推荐班级《{$classroom['title']}》(#{$classroom['id']}),序号为{$number}");

        return $classroom;
    }

    public function cancelRecommendClassroom($id)
    {
        $this->tryAdminClassroom($id);

        $classroom = $this->getClassroomDao()->updateClassroom($id, array(
            'recommended'     => 0,
            'recommendedTime' => 0,
            'recommendedSeq'  => 100
        ));

        $this->getLogService()->info('classroom', 'cancel_recommend', "取消推荐班级《{$classroom['title']}》(#{$classroom['id']})");

        return $classroom;
    }

    public function tryAdminClassroom($classroomId)
    {
        $classroom = $this->getClassroomDao()->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if (empty($user->id)) {
            throw $this->createAccessDeniedException('未登录用户，无权操作！');
        }

        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) == 0) {
            throw $this->createAccessDeniedException('您不是管理员，无权操作！');
        }

        return $classroom;
    }

    public function getClassroomMembersByCourseId($courseId, $userId)
    {
        $classroomIds = $this->findClassroomIdsByCourseId($courseId);
        $members      = $this->findMembersByUserIdAndClassroomIds($userId, $classroomIds);
        return $members;
    }

    private function updateStudentNumAndAuditorNum($classroomId)
    {
        $fields = array(
            'studentNum' => $this->getClassroomStudentCount($classroomId),
            'auditorNum' => $this->getClassroomAuditorCount($classroomId)
        );

        return $this->getClassroomDao()->updateClassroom($classroomId, $fields);
    }

    private function addCourse($id, $courseId)
    {
        $course          = $this->getCourseService()->getCourse($courseId);
        $classroomCourse = array(
            'classroomId'    => $id,
            'courseId'       => $courseId,
            'parentCourseId' => $course['parentId']
        );

        $classroomCourse = $this->getClassroomCourseDao()->addCourse($classroomCourse);

        $this->dispatchEvent('classroom.put_course', $classroomCourse);
    }

    public function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomDao');
    }

    protected function getClassroomMemberDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomMemberDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getCourseCopyService()
    {
        return $this->createService('Course.CourseCopyService');
    }

    protected function getClassroomCourseDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomCourseDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    protected function getVipService()
    {
        return $this->createService('Vip:Vip.VipService');
    }

    protected function getNoteDao()
    {
        return $this->createDao('Course.CourseNoteDao');
    }

    protected function getStatusService()
    {
        return $this->createService('User.StatusService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy.CategoryService');
    }
}

class MemberSerialize
{
    public static function serialize(array $member)
    {
        $member['role'] = empty($member['role']) ? '' : '|'.implode('|', $member['role']).'|';
        return $member;
    }

    public static function unserialize(array $member = null)
    {
        if (empty($member)) {
            return null;
        }

        $member['role'] = empty($member['role']) ? array() : explode('|', trim($member['role'], '|'));
        return $member;
    }

    public static function unserializes(array $members)
    {
        return array_map(function ($member) {
            return MemberSerialize::unserialize($member);
        }, $members);
    }
}
