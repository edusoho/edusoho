<?php


namespace ApiBundle\Api\Resource\Assistant;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use ApiBundle\Api\Annotation\Access;

class Assistant extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @return array
     * @Access(roles="ROLE_TEACHER_ASSISTANT,ROLE_TEACHER,ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function search(ApiRequest $request)
    {
        $conditions = [
            'nickname' => $request->query->get('nickname', ''),
            'role' => 'ROLE_TEACHER_ASSISTANT',
            'destroyed' => 0,
            'locked' => 0,
            'excludeIds' => $request->query->get('excludeIds', []),
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
}