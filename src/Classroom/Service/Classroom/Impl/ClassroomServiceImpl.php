<?php

namespace Classroom\Service\Classroom\Impl;

use Topxia\Service\Common\BaseService;
use Classroom\Service\Classroom\ClassroomService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\File\File;

class ClassroomServiceImpl extends BaseService implements ClassroomService
{
    public function getClassroom($id)
    {
        $classroom = $this->getClassroomDao()->getClassroom($id);

        return $classroom;
    }

    public function searchClassrooms($conditions, $orderBy, $start, $limit)
    {
        return $this->getClassroomDao()->searchClassrooms($conditions, $orderBy, $start, $limit);
    }

    public function searchClassroomsCount($conditions)
    {
        $count = $this->getClassroomDao()->searchClassroomsCount($conditions);

        return $count;
    }

    public function findClassroomsByCourseId($courseId)
    {
        return $this->getClassroomCourseDao()->findClassroomsByCourseId($courseId);
    }

    public function findClassroomByCourseId($courseId)
    {
        return $this->getClassroomCourseDao()->findClassroomByCourseId($courseId);
    }

    public function findAssistants($classroomId)
    {
        return $this->getClassroomMemberDao()->findAssistants($classroomId);
    }

    public function addClassroom($classroom)
    {
        $title = trim($classroom['title']);
        if (empty($title)) {
            throw $this->createServiceException("班级名称不能为空！");
        }

        $classroom['createdTime'] = time();
        $classroom = $this->getClassroomDao()->addClassroom($classroom);

        return $classroom;
    }

    public function addCoursesToClassroom($classroomId, $courseIds)
    {
        $this->tryManageClassroom($classroomId);
        $this->getClassroomDao()->getConnection()->beginTransaction();
        try {
            //find Existing Courses, open it and active it
            $allExistingCourses = $this->findCoursesByClassroomId($classroomId);
            $existCourseIds = array();
            $existCourseParentIds = array();
            foreach ($allExistingCourses as $key => $existCourse) {
                if (in_array($existCourse['parentId'], $courseIds)) {
                    $existCourseIds[$existCourse['parentId']] = $existCourse['id'];
                    $existCourseParentIds[] = $existCourse['parentId'];
                }
            }

            $sameCourseIds = array_intersect($existCourseParentIds, $courseIds);
            foreach ($sameCourseIds as $key => $courseId) {
                $courseId = $existCourseIds[$courseId];
                $this->getClassroomCourseDao()->updateByParam(array('classroomId' => $classroomId, 'courseId' => $courseId), array('disabled' => 0));
                $this->getCourseService()->publishCourse($courseId);
            }

            $courseIds = array_values(array_diff($courseIds, $sameCourseIds));
            //if new copy it
            if (!empty($courseIds)) {
                $courses = $this->getCourseService()->findCoursesByIds($courseIds);
                $newCourseIds = array();
                foreach ($courses as $key => $course) {
                    $newCourse = $this->getCourseCopyService()->copy($course, true);
                    $newCourseIds[] = $newCourse['id'];
                }
                $this->setClassroomCourses($classroomId, $newCourseIds);
            }

            $courses = $this->findActiveCoursesByClassroomId($classroomId);
            $coursesNum = count($courses);

            $lessonNum = 0;
            foreach ($courses as $key => $course) {
                $lessonNum += $course['lessonNum'];
            }

            $this->updateClassroom($classroomId, array("courseNum" => $coursesNum, "lessonNum" => $lessonNum));

            $this->updateClassroomTeachers($classroomId);

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

        $fields = ArrayToolkit::parts($fields, array('rating', 'ratingNum', 'categoryId', 'title', 'status', 'about', 'description', 'price', 'vipLevelId', 'smallPicture', 'middlePicture', 'largePicture', 'headTeacherId', 'teacherIds', 'hitNum', 'auditorNum', 'studentNum', 'courseNum', 'lessonNum', 'threadNum', 'postNum', 'income', 'createdTime', 'private', 'service'));

        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新失败！');
        }

        $classroom = $this->getClassroomDao()->updateClassroom($id, $fields);

        return $classroom;
    }

    public function waveClassroom($id, $field, $diff)
    {
        return $this->getClassroomDao()->waveClassroom($id, $field, $diff);
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
            throw $this->createServiceException("班级不存在，操作失败。");
        }

        if ($classroom['status'] != 'draft') {
            throw $this->createServiceException("只有未发布班级可以删除，操作失败。");
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

        $teacherIds = $classroom['teacherIds'] ?: array();

        $ids = array();
        foreach ($teacherIds as $key => $value) {
            $isCourseInClassroom = $this->isCourseInClassroom($value, $id);

            if (!$isCourseInClassroom) {
                unset($teacherIds[$key]);
            }
        }

        foreach ($courses as $key => $value) {
            $course = $this->getCourseService()->getCourse($value['id']);

            $teacherIds = array_merge($teacherIds, $course['teacherIds']);
        }

        $teacherIds = array_unique($teacherIds);

        foreach ($teacherIds as $key => $value) {
            $ids[] = $value;
        }

        $teacherIds = $ids;

        $classroomTeacherIds = $classroom['teacherIds'] ?: array();
        if (count($classroomTeacherIds) > count($ids)) {
            $diff = array_diff($classroomTeacherIds, $ids);
            foreach ($diff as $key => $value) {
                $this->getClassroomMemberDao()->deleteMemberByClassroomIdAndUserId($id, $value);
            }
        } else {
            $diff = array_diff($ids, $classroomTeacherIds);
            foreach ($diff as $key => $value) {
                $fields = array(
                    'classroomId' => $id,
                    'userId' => $value,
                    'role' => 'teacher',
                    'createdTime' => time(),
                );

                $member = $this->getClassroomMemberDao()->getMemberByClassroomIdAndUserId($id, $value);

                if ($member) {
                    $member = $this->getClassroomMemberDao()->updateMember($member['id'], $fields);
                } else {
                    $member = $this->getClassroomMemberDao()->addMember($fields);
                }
            }
        }

        $this->updateClassroom($id, array('teacherIds' => $teacherIds));
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

    public function changePicture ($id, $data)
    {
        $classroom = $this->getClassroomDao()->getClassroom($id);
        if (empty($classroom)) {
            throw $this->createServiceException('班级不存在，图标更新失败！');
        }

        $fileIds = ArrayToolkit::column($data, "id");
        $files = $this->getFileService()->getFilesByIds($fileIds);

        $files = ArrayToolkit::index($files, "id");
        $fileIds = ArrayToolkit::index($data, "type");

        $fields = array(
            'smallPicture' => $files[$fileIds["small"]["id"]]["uri"],
            'middlePicture' => $files[$fileIds["middle"]["id"]]["uri"],
            'largePicture' => $files[$fileIds["large"]["id"]]["uri"]
        );

        $this->deleteNotUsedPictures($classroom);

        $this->getLogService()->info('classroom', 'update_picture', "更新课程《{$classroom['title']}》(#{$classroom['id']})图片", $fields);
        
        return $this->updateClassroom($id,$fields);
    }

    private function deleteNotUsedPictures($classroom)
    {
        $oldPictures = array(
            'smallPicture' => $classroom['smallPicture'] ? $classroom['smallPicture'] : null,
            'middlePicture' => $classroom['middlePicture'] ? $classroom['middlePicture'] : null,
            'largePicture' => $classroom['largePicture'] ? $classroom['largePicture'] : null
        );


        array_map(function($oldPicture){
            if (!empty($oldPicture)){
                $this->getFileService()->deleteFileByUri($oldPicture);
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
        $courses = $this->findCoursesByClassroomId($classroomId);
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
        return ArrayToolkit::index($this->getClassroomMemberDao()->findMembersByUserIdAndClassroomIds($userId, $classroomIds), 'classroomId');
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

        return $this->getClassroomMemberDao()->searchMembers($conditions, $orderBy, $start, $limit);
    }

    public function getClassroomMember($classroomId, $userId)
    {
        return $this->getClassroomMemberDao()->getMemberByClassroomIdAndUserId($classroomId, $userId);
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
            throw $this->createServiceException("班级不存在，操作失败。");
        }

        $member = $this->getClassroomMember($classroomId, $userId);

        if (empty($member) or !(in_array($member['role'], array('student', 'auditor')))) {
            throw $this->createServiceException("用户(#{$userId})不是班级(#{$classroomId})的学员，退出班级失败。");
        }

        $this->getClassroomMemberDao()->deleteMember($member['id']);

        $classroom = $this->updateStudentNumAndAudtorNum($classroomId);

        $this->getLogService()->info('classroom', 'remove_student', "班级《{$classroom['title']}》(#{$classroom['id']})，移除学员#{$member['id']}");
    }

    public function isClassroomStudent($classroomId, $userId)
    {
        $member = $this->getClassroomMember($classroomId, $userId);
        if (!$member) {
            return false;
        } else {
            return empty($member) or $member['role'] != 'student' ? false : true;
        }
    }

    // becomeStudent的逻辑条件，写注释
    public function becomeStudent($classroomId, $userId, $info = array())
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
            'userId' => $userId,
            'orderId' => empty($order) ? 0 : $order['id'],
            'levelId' => empty($info['becomeUseMember']) ? 0 : $userMember['levelId'],
            'role' => 'student',
            'remark' => empty($order['note']) ? '' : $order['note'],
            'createdTime' => time(),
        );

        if (empty($fields['remark'])) {
            $fields['remark'] = empty($info['note']) ? '' : $info['note'];
        }

        if (!empty($member)) {
            $member = $this->getClassroomMemberDao()->updateMember($member['id'], $fields);
        } else {
            $member = $this->getClassroomMemberDao()->addMember($fields);
        }

        $courses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);

        $courseIds = ArrayToolkit::column($courses, "courseId");

        foreach ($courseIds as $key => $courseId) {
            $courseMember = $this->getCourseService()->getCourseMember($courseId, $userId);
            if (empty($courseMember)) {
                $info = array(
                    'orderId' => empty($order) ? 0 : $order['id'],
                    'orderNote' => empty($order['note']) ? '' : $order['note'],
                );
                $this->getCourseService()->becomeStudentByClassroomJoined($courseId, $userId, $classroomId, $info);
            }
        }

        $fields = array(
            'studentNum' => $this->getClassroomStudentCount($classroomId),
            'auditorNum' => $this->getClassroomAuditorCount($classroomId),
        );
        if ($order) {
            $income = $this->getOrderService()->sumOrderPriceByTarget('classroom', $classroomId);
            $fields['income'] = empty($income) ? 0 : $income;
        }
        $this->getClassroomDao()->updateClassroom($classroomId, $fields);
        $this->dispatchEvent(
            'classroom.join',
            new ServiceEvent($classroom, array('userId' => $member['userId']))
        );

        return $member;
    }

    public function updateClassroomCourses($classroomId, $activeCourseIds)
    {
        $this->tryManageClassroom($classroomId);
        $this->getClassroomDao()->getConnection()->beginTransaction();
        try {
            $courses = $this->findActiveCoursesByClassroomId($classroomId);
            $courses = ArrayToolkit::index($courses, 'id');
            $existCourseIds = ArrayToolkit::column($courses, 'id');

            $diff = array_diff($existCourseIds, $activeCourseIds);
            if (!empty($diff)) {
                foreach ($diff as $courseId) {
                    $this->getClassroomCourseDao()->update($courses[$courseId]['classroom_course_id'], array('disabled' => 1));
                    $this->getCourseService()->closeCourse($courseId);
                }

                $courses = $this->findActiveCoursesByClassroomId($classroomId);
                $coursesNum = count($courses);

                $lessonNum = 0;
                foreach ($courses as $key => $course) {
                    $lessonNum += $course['lessonNum'];
                }

                $this->updateClassroom($classroomId, array("courseNum" => $coursesNum, "lessonNum" => $lessonNum));

                $this->updateClassroomTeachers($classroomId);
            }

            $this->getClassroomDao()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getClassroomDao()->getConnection()->rollback();
            throw $e;
        }
    }

    public function findCoursesByCoursesIds($courseIds)
    {
        return $this->getClassroomCourseDao()->findCoursesByCoursesIds($courseIds);
    }

    public function findCoursesByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($classroomCourses, "courseId");
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, "id");
        $sordtedCourses = array();
        foreach ($classroomCourses as $key => $classroomCourse) {
            $sordtedCourses[$key] = $courses[$classroomCourse["courseId"]];
        }

        return $sordtedCourses;
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $sordtedCourses = array();
        foreach ($classroomCourses as $key => $classroomCourse) {
            $sordtedCourses[$key] = $courses[$classroomCourse['courseId']];
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

    public function isClassroomTeacher($classroomId, $userId)
    {
        $member = $this->getClassroomMember($classroomId, $userId);
        if (!$member) {
            return false;
        } else {
            return empty($member) or $member['role'] != 'teacher' ? false : true;
        }
    }

    public function addHeadTeacher($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if ($classroom['headTeacherId']) {
            $this->getClassroomMemberDao()->deleteMemberByClassroomIdAndUserId($classroomId, $classroom['headTeacherId']);
        }

        if (!empty($userId)) {
            $this->updateClassroom($classroomId, array('headTeacherId' => $userId ));

            $member = $this->getClassroomMember($classroomId, $userId);
            $fields = array(
                'classroomId' => $classroomId,
                'userId' => $userId,
                'orderId' => 0,
                'levelId' => 0,
                'role' => 'headTeacher',
                'remark' => '',
                'createdTime' => time(),
            );

            if ($member) {
                $member = $this->getClassroomMemberDao()->updateMember($member['id'], $fields);
            } else {
                $member = $this->getClassroomMemberDao()->addMember($fields);
            }
        }
    }

    public function updateAssistants($classroomId, $userIds)
    {
        $assistants = $this->findAssistants($classroomId);
        $assistants = ArrayToolkit::index($assistants, 'userId');
        $assistantIds = ArrayToolkit::column($assistants, 'userId');
        foreach ($assistants as $key => $assistant) {
            if ('assistant' == $assistant['role']) {
                $this->getClassroomMemberDao()->deleteMember($assistant['id']);
            }

            if ('studentAssistant' == $assistant['role']) {
                $this->getClassroomMemberDao()->updateMember($assistant['id'], array('role' => 'student'));
            }
        }

        $members = $this->findMembersByClassroomIdAndUserIds($classroomId, $userIds);
        foreach ($userIds as $userId) {
            if (!empty($members[$userId]) && $members[$userId]['role'] == 'student') {
                $this->getClassroomMemberDao()->updateMember($members[$userId]['id'], array('role' => 'studentAssistant'));
                continue;
            }

            if (!empty($members[$userId])) {
                $this->getClassroomMemberDao()->updateMember($members[$userId]['id'], array('role' => 'assistant'));
                continue;
            }

            $this->becomeAssistant($classroomId, $userId);
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
            'userId' => $userId,
            'orderId' => 0,
            'levelId' => 0,
            'role' => 'auditor',
            'remark' => '',
            'createdTime' => time(),
        );

        $member = $this->getClassroomMemberDao()->addMember($fields);

        $classroom = $this->updateStudentNumAndAudtorNum($classroomId);
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

        if ($classroom['status'] != 'published') {
            throw $this->createServiceException('不能加入未发布班级');
        }

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException("用户(#{$userId})不存在，加入班级失败！");
        }

        $fields = array(
            'classroomId' => $classroomId,
            'userId' => $userId,
            'orderId' => 0,
            'levelId' => 0,
            'role' => 'assistant',
            'remark' => '',
            'createdTime' => time(),
        );

        $member = $this->getClassroomMemberDao()->addMember($fields);

        $this->dispatchEvent(
            'classroom.become_assistant',
            new ServiceEvent($classroom, array('userId' => $member['userId']))
        );

        return $member;
    }

    public function isClassroomAuditor($classroomId, $studentId)
    {
        $member = $this->getClassroomMember($classroomId, $studentId);
        if (!$member) {
            return false;
        } else {
            return empty($member) or $member['role'] != 'auditor' ? false : true;
        }
    }

    private function _prepareClassroomConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    private function canBecomeClassroomMember($member)
    {
        return empty($member) || !in_array($member["role"], array("student", "teacher", "headTeacher", "assistant", "studentAssistant"));
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

        if (in_array($member['role'], array('headTeacher'))) {
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

    public function canTakeClassroom($id)
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

        if (in_array($member['role'], array('student', 'teacher', 'headTeacher'))) {
            return true;
        }

        return false;
    }

    public function tryTakeClassroom($id)
    {
        if (!$this->canTakeClassroom($id)) {
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
        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $member = $this->getClassroomMember($id, $user['id']);
        if ($member) {
            return true;
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

        return in_array($member['role'], array('teacher', 'headTeacher', 'assistant', 'studentAssistant'));
    }

    // @todo 写逻辑条件的注释
    public function exitClassroom($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $member = $this->getClassroomMemberDao()->getMemberByClassroomIdAndUserId($classroomId, $userId);

        if (!$member) {
            throw $this->createAccessDeniedException('您不是班级学员，无法退出班级！');
        }

        if ($member['role'] == "teacher" || $member['role'] == "headerTeach") {
            throw $this->createAccessDeniedException('教师无法退出班级！');
        }

        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);

        $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');

        foreach ($courseIds as $key => $courseId) {
            $count = 0;
            $courseMember = $this->getCourseService()->getCourseMember($courseId, $userId);

            if ($courseMember && $courseMember['joinedType'] == "course") {
                unset($courseIds[$key]);
                continue;
            }

            $classroomIds = $this->getClassroomCourseDao()->findClassroomsByCourseId($courseId);

            $classroomIds = ArrayToolkit::column($classroomIds, 'classroomId');

            foreach ($classroomIds as $value) {
                if ($classroomId == $value) {
                    continue;
                }

                $member = $this->getClassroomMemberDao()->getMemberByClassroomIdAndUserId($value, $userId);

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

        $this->getClassroomMemberDao()->deleteMemberByClassroomIdAndUserId($classroomId, $userId);

        $this->updateStudentNumAndAudtorNum($classroomId);
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
        return $this->getClassroomMemberDao()->findMembersByClassroomIdAndRole($classroomId, 'student', $start, $limit);
    }

    public function findClassroomMembersByRole($classroomId, $role, $start, $limit)
    {
        return ArrayToolkit::index($this->getClassroomMemberDao()->findMembersByClassroomIdAndRole($classroomId, $role, $start, $limit), 'userId');
    }

    public function findMembersByClassroomIdAndUserIds($classroomId, $userIds)
    {
        return ArrayToolkit::index($this->getClassroomMemberDao()->findMembersByClassroomIdAndUserIds($classroomId, $userIds), 'userId');
    }

    public function lockStudent($classroomId, $userId)
    {
        $classroom = $this->getClassroom($classroomId);
        if (empty($classroom)) {
            throw $this->createNotFoundException("班级(#${$classroomId})不存在，封锁学员失败。");
        }

        $member = $this->getClassroomMember($classroomId, $userId);
        if (empty($member) or ($member['role'] != 'student')) {
            throw $this->createServiceException("用户(#{$userId})不是班级(#{$courseId})的学员，封锁学员失败。");
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
            throw $this->createNotFoundException("班级(#${$courseId})不存在，封锁学员失败。");
        }

        $member = $this->getClassroomMember($classroomId, $userId);
        if (empty($member) or ($member['role'] != 'student')) {
            throw $this->createServiceException("用户(#{$userId})不是该班级(#{$courseId})的学员，解封学员失败。");
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
            'recommended' => 1,
            'recommendedSeq' => (int)$number,
            'recommendedTime' => time(),
        ));

        $this->getLogService()->info('classroom', 'recommend', "推荐班级《{$classroom['title']}》(#{$classroom['id']}),序号为{$number}");

        return $classroom;
    }

    public function cancelRecommendClassroom($id)
    {
        $this->tryAdminClassroom($id);

        $classroom = $this->getClassroomDao()->updateClassroom($id, array(
            'recommended' => 0,
            'recommendedTime' => 0,
            'recommendedSeq' => 100,
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

    private function updateStudentNumAndAudtorNum($classroomId){

        $fields = array(
            'studentNum' => $this->getClassroomStudentCount($classroomId),
            'auditorNum' => $this->getClassroomAuditorCount($classroomId),
        );

        return $this->getClassroomDao()->updateClassroom($classroomId, $fields);
    }

    private function addCourse($id, $courseId)
    {
        $classroomCourse = array(
            'classroomId' => $id,
            'courseId' => $courseId, );

        $this->getClassroomCourseDao()->addCourse($classroomCourse);
    }

    protected function getFileService()
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

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    private function getVipService()
    {
        return $this->createService('Vip:Vip.VipService');
    }

    private function getNoteDao()
    {
        return $this->createDao('Course.CourseNoteDao');
    }

    private function getStatusService()
    {
        return $this->createService('User.StatusService');
    }
}
