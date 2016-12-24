<?php


namespace Biz\Course\Service;


interface MemberService
{
    public function becomeStudentAndCreateOrder($userId, $courseId, $data);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function searchMember($conditions, $start, $limit);

    public function searchMemberCount($conditions);

    public function searchMemberCountGroupByFields($conditions, $groupBy, $start, $limit);

    public function findWillOverdueCourses();

    public function getCourseMember($courseId, $userId);

    public function searchMemberIds($conditions, $sort, $start, $limit);

    public function findMemberUserIdsByCourseId($courseId);

    public function updateCourseMember($id, $fields);

    public function updateMembers($conditions, $updateFields);

    public function isMemberNonExpired($course, $member);

    public function findCourseStudents($courseId, $start, $limit);

    public function findCourseStudentsByCourseIds($courseIds);

    public function getCourseStudentCount($courseId);

    public function isCourseTeacher($courseId, $userId);

    public function isCourseStudent($courseId, $userId);

    public function isCourseMember($courseId, $userId);

    public function setCourseTeachers($courseId, $teachers);

    public function cancelTeacherInAllCourses($userId);

    public function remarkStudent($courseId, $userId, $remark);

    public function deleteMemberByCourseIdAndRole($courseId, $role);

    public function deleteMemberByCourseId($courseId);

    public function findMembersByUserIdAndJoinType($userId, $joinedType = 'course');

    public function quitCourseByDeadlineReach($userId, $courseId);

    /**
     * 成为学员，即加入课程的学习
     */
    public function becomeStudent($courseId, $userId);

    /**
     * 退学
     */
    public function removeStudent($courseId, $userId);

    /**
     * 封锁学员，封锁之后学员不能再查看该课程
     */
    public function lockStudent($courseId, $userId);

    /**
     * 解封学员
     */
    public function unlockStudent($courseId, $userId);

    public function createMemberByClassroomJoined($courseId, $userId, $classRoomId, array $info);

    public function findCoursesByStudentIdAndCourseIds($studentId, $courseIds);

    public function becomeStudentByClassroomJoined($courseId, $userId);

    public function setMemberNoteNumber($courseId, $userId, $number);
}