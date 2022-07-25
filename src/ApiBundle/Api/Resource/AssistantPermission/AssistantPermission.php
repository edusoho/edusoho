<?php


namespace ApiBundle\Api\Resource\AssistantPermission;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Common\CommonException;
use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;

class AssistantPermission extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @return array
     */
    public function get(ApiRequest $request, $portal)
    {
        $assistant = $this->biz['assistant_permission'];

        return [
            'isAssistant' => $assistant->isAssistant(),
            'permissions' => $assistant->getPermissions(),
        ];
    }

    /**
     * @param ApiRequest $request
     * @return array
     */
    public function search(ApiRequest $request)
    {
        $assistant = $this->biz['assistant_permission'];

        return [
            'menu' => $assistant->getPermissionMenu(),
            'permissions' => $assistant->getPermissions()
        ];
    }

    /**
     * @param ApiRequest $request
     * @return array
     */
    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->hasPermission('admin_v2_assistant_manage')) {
            throw new AccessDeniedException();
        }

        $permissions = $request->request->get('permissions');
        if (empty($permissions)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $this->getSettingService()->set('assistant_permission', ['permissions' => $permissions]);

        $assistantPermissions = $this->getSettingService()->get('assistant_permission');

        return $assistantPermissions['permissions'];
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
}