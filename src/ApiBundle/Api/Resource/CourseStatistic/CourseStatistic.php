<?php

namespace ApiBundle\Api\Resource\CourseStatistic;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class CourseStatistic extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $userIds = $this->getUserService()->searchUsers(['isStudent' => 1], ['createdTime' => 'DESC'], $offset, $limit);
        $courseMembers = $this->getMemberService()->searchMembers(['userIds' => array_column($userIds, 'id')], [], 0, PHP_INT_MAX);
        $courseMembersGrouped = ArrayToolkit::group($courseMembers, 'userId');
        $groupedMembers = [];
        foreach ($courseMembersGrouped as $userId => &$courseMember) {
            if (!isset($groupedMembers[$userId])) {
                $groupedMembers[$userId] = [
                    'learned' => 0,
                    'notLearned' => 0,
                    'expired' => 0,
                ];
            }
            if ($courseMember['isLearned']) {
                ++$groupedMembers[$userId]['learned'];
            } else {
                ++$groupedMembers[$userId]['notLearned'];
            }
            if ($courseMember['deadline'] < time()) {
                ++$groupedMembers[$userId]['expired'];
            }
        }

        return $groupedMembers;
    }

    /**
     * @return MemberService
     */
    private function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
