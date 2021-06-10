<?php


namespace ApiBundle\Api\Resource\Teacher;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\User\Service\UserService;
use ApiBundle\Api\Annotation\Access;

class Teacher extends AbstractResource
{
    /**
     * @param ApiRequest $request
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