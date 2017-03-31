<?php

namespace Biz\Course\CourseProcessor;

use AppBundle\Common\ArrayToolkit;

class MemberProcessor extends BaseCourseProcessor
{
    public function getCourseMemberInfo($request, $container)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 100);
        $courseId = $request->query->get('courseId');
        $course = $this->getCourseService()->getCourse($courseId);
        $sourceCourseMembers = $this->getCourseMemberService()->findMemberByCourseId($course['id']);
        $userIds = ArrayToolkit::column($sourceCourseMembers, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $result = $this->buildNeedCourseMemberFields($sourceCourseMembers, $users, $container);
        $result['start'] = $start + $limit;
        $result['limit'] = $limit;
        if (count($result['data']) < $limit) {
            $result['finish'] = true;
        } else {
            $result['finish'] = false;
        }
        return $result;
    }

    protected function buildNeedCourseMemberFields($sourceCourseMembers, $users, $container)
    {
        $result = array();
        $filter = array( 'nickname' => '', 'smallAvatar' => '', 'id' => 0, 'role' => '');
        $sourceCourseMembers = ArrayToolkit::index($sourceCourseMembers, 'userId');
        $users = ArrayToolkit::index($users, 'id');
        foreach ($sourceCourseMembers as $userId => $sourceCourseMember) {
            $avatar = $container->get('web.twig.extension')->getFpath($users[$userId]['smallAvatar']);
            $courseMember['clientName'] = $users[$userId]['nickname'];
            $courseMember['avatar'] = $_SERVER['HTTP_HOST'].$avatar;
            $courseMember['clientId'] = $userId;
            $courseMember['role'] = $sourceCourseMember['role'];
            $result['data'][] = $courseMember;
        }

        return $result;
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
