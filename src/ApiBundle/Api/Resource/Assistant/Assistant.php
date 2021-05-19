<?php


namespace ApiBundle\Api\Resource\Assistant;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\MemberService;
use Biz\User\Service\UserService;

class Assistant extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $assistants = $this->getCourseMemberService()->searchMembers(['role' => 'assistant'], [], 0, PHP_INT_MAX);

        $conditions = [
            'nickname' => $request->query->get('nickname', ''),
            'userIds' => array_unique(array_column($assistants, 'userId')),
        ];

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $users = $this->getUserService()->searchUsers($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $total = $this->getUserService()->countUsers($conditions);

        return $this->makePagingObject($users, $total, $offset, $limit);

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
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}