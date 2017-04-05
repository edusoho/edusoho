<?php

namespace AppBundle\Controller\Callback\CourseLive\Resource;

use AppBundle\Controller\Callback\CourseLive\BaseProvider;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseMember extends BaseProvider
{
    public function get(Request $request)
    {
        $token = $request->query->get('token');
        $courseId = $request->query->get('courseId');
        $this->checkToken($token);

        $userToken = $this->getTokenService()->getByToken($token);
        if ($userToken['data'] != $courseId) {
            throw new \RuntimeException(sprintf('只能查看课程id为%s的成员', $userToken['data']));
        }
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 100);
        $courseId = $request->query->get('courseId');

        $course = $this->getCourseService()->getCourse($courseId);
        $sourceCourseMembers = $this->getCourseMemberService()->findMemberByCourseId($course['id']);

        $userIds = ArrayToolkit::column($sourceCourseMembers, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $result = $this->buildNeedCourseMemberFields($sourceCourseMembers, $users);
        $result['start'] = $start + $limit;
        $result['limit'] = $limit;
        if (count($result['data']) < $limit) {
            $result['finish'] = true;
        } else {
            $result['finish'] = false;
        }
        return $result;
    }

    protected function checkToken($token)
    {
        $isTrue = $this->getTokenService()->verifyToken('live.create', $token);

        if (!$isTrue) {
            throw new \RuntimeException('Token不正确！');
        }
    }

    protected function buildNeedCourseMemberFields($sourceCourseMembers, $users)
    {
        $result = array();
        $sourceCourseMembers = ArrayToolkit::index($sourceCourseMembers, 'userId');
        $users = ArrayToolkit::index($users, 'id');
        foreach ($sourceCourseMembers as $userId => $sourceCourseMember) {
            $courseMember['clientName'] = $users[$userId]['nickname'];
            $courseMember['avatar'] = $this->getFileUrl($users[$userId]['smallAvatar'], 'avatar.png');
            $courseMember['clientId'] = $userId;
            $courseMember['role'] = $sourceCourseMember['role'];
            
            $result['data'][] = $courseMember;
        }

        return $result;
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
