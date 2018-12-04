<?php

namespace AppBundle\Controller\Callback\AthenaLive;

use AppBundle\Common\ArrayToolkit;
use Biz\User\TokenException;
use Symfony\Component\HttpFoundation\Request;

class Members extends AthenaLiveBase
{
    public function fetch(Request $request)
    {
        $token = $request->query->get('token');
        $courseId = $request->query->get('courseId');

        $userToken = $this->getTokenService()->verifyToken('live.callback', $token);

        if (!$userToken) {
            $this->createNewException(TokenException::TOKEN_INVALID());
        }

        if ($userToken['data']['courseId'] != $courseId) {
            $this->createNewException(TokenException::NOT_MATCH_COURSE());
        }

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 200);

        if ($userToken['data']['type'] == 'open_course') {
            $sourceCourseMembers = $this->getOpenCourseService()->searchMembers(
                array('courseId' => $courseId),
                array('createdTime' => 'DESC'),
                $start,
                $limit
            );
        } else {
            $sourceCourseMembers = $this->getCourseMemberService()->searchMembers(
                array('courseId' => $courseId),
                array('createdTime' => 'DESC'),
                $start,
                $limit
            );
        }

        $userIds = ArrayToolkit::column($sourceCourseMembers, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $result = $this->buildCourseMemberData($sourceCourseMembers, $users);
        if (empty($result)) {
            $result['data'] = array();
            $result['finish'] = true;

            return $result;
        }
        $result['start'] = $start + $limit;
        $result['limit'] = $limit;
        if (count($result['data']) < $limit) {
            $result['finish'] = true;
        } else {
            $result['finish'] = false;
        }

        return $result;
    }

    protected function buildCourseMemberData($sourceCourseMembers, $users)
    {
        $result = array();
        $sourceCourseMembers = ArrayToolkit::index($sourceCourseMembers, 'userId');
        $users = ArrayToolkit::index($users, 'id');
        foreach ($sourceCourseMembers as $userId => $sourceCourseMember) {
            $courseMember['clientName'] = $users[$userId]['nickname'];
            // $courseMember['avatar'] = $this->getFileUrl($users[$userId]['smallAvatar'], 'avatar.png');
            $courseMember['clientId'] = $userId;
            $courseMember['role'] = $sourceCourseMember['role'];

            $result['data'][] = $courseMember;
        }

        return $result;
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\User\Service\TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return \Biz\OpenCourse\Service\OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return \Biz\Course\Service\MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
