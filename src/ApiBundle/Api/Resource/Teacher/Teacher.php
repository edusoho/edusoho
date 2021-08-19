<?php

namespace ApiBundle\Api\Resource\Teacher;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\UserService;

class Teacher extends AbstractResource
{
    /**
     * @return array
     */
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2')) {
            throw new AccessDeniedException();
        }

        $conditions = [
            'nickname' => $request->query->get('nickname', ''),
            'roles' => '|ROLE_TEACHER|',
            'destroyed' => 0,
            'locked' => 0,
            'excludeIds' => $request->query->get('excludeIds', []),
        ];

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $users = $this->getUserService()->searchUsers($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $total = $this->getUserService()->countUsers($conditions);

        $users = $this->appendTeacherData($users);

        return $this->makePagingObject($users, $total, $offset, $limit);
    }

    protected function appendTeacherData($teachers)
    {
        $currentTime = time();
        $members = $this->getMemberService()->findMembersByUserIdsAndRole(array_column($teachers, 'id'), 'teacher');

        $courseIds = empty($members) ? [-1] : ArrayToolkit::column($members, 'courseId');
        $liveMultiClasses = $this->findMultiClassesByConditions(['courseIds' => $courseIds, 'endTimeLT' => $currentTime]);
        $endMultiClasses = $this->findMultiClassesByConditions(['courseIds' => $courseIds, 'endTimeGE' => $currentTime]);

        $liveMultiClassStudentCount = $this->findCourseStudentCount(ArrayToolkit::column($liveMultiClasses, 'courseId'));
        $endMultiClassStudentCount = $this->findCourseStudentCount(ArrayToolkit::column($endMultiClasses, 'courseId'));
        $members = ArrayToolkit::group($members, 'userId');
        foreach ($teachers as &$teacher) {
            $teacherMembers = empty($members[$teacher['id']]) ? [] : $members[$teacher['id']];
            $liveMultiClassStudentNum = 0;
            $endMultiClassStudentNum = 0;
            $liveMultiClassNum = 0;
            $endMultiClassNum = 0;
            foreach ($teacherMembers as $teacherMember) {
                $liveMultiClassStudentNum += empty($liveMultiClassStudentCount[$teacherMember['courseId']]) ? 0 : $liveMultiClassStudentCount[$teacherMember['courseId']]['count'];
                $endMultiClassStudentNum += empty($endMultiClassStudentCount[$teacherMember['courseId']]) ? 0 : $endMultiClassStudentCount[$teacherMember['courseId']]['count'];
                $liveMultiClassNum += empty($liveMultiClasses[$teacherMember['courseId']]) ? 0 : 1;
                $endMultiClassNum += empty($endMultiClasses[$teacherMember['courseId']]) ? 0 : 1;
            }
            $teacher['liveMultiClassStudentNum'] = $liveMultiClassStudentNum;
            $teacher['endMultiClassStudentNum'] = $endMultiClassStudentNum;
            $teacher['liveMultiClassNum'] = $liveMultiClassNum;
            $teacher['endMultiClassNum'] = $endMultiClassNum;
        }

        return $teachers;
    }

    protected function findMultiClassesByConditions($conditions)
    {
        $multiClasses = $this->getMultiClassService()->searchMultiClass(
            $conditions,
            [],
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::index($multiClasses, 'courseId');
    }

    protected function findCourseStudentCount($courseIds)
    {
        if (empty($courseIds)) {
            return [];
        }

        $courseStudentNum = $this->getMemberService()->searchMemberCountGroupByFields(
            ['courseIds' => $courseIds, 'role' => 'student'],
            'courseId',
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::index($courseStudentNum, 'courseId');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }
}
