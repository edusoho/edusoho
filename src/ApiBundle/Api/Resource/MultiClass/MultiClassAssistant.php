<?php


namespace ApiBundle\Api\Resource\MultiClass;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\UserService;

class MultiClassAssistant extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @param $multiClassId
     * @return mixed
     */
    public function search(ApiRequest $request, $multiClassId)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_education')){
            throw new AccessDeniedException();
        }

        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $assistants = $this->getCourseMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClassId, 'assistant');
        $userIds = ArrayToolkit::column($assistants, 'userId');
        $conditions = [
            'userIds' => $userIds,
            'nickname' => $request->query->get('nickname', '')
        ];

        $users = $this->getUserService()->searchUsers($conditions, ['createdTime' => 'DESC'], 0, count($userIds));
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filters($users);

        return $users;
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}