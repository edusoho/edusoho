<?php


namespace ApiBundle\Api\Resource\AssistantPermission;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use ApiBundle\Api\Annotation\Access;

class AssistantPermission extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @return array
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN,ROLE_TEACHER,ROLE_TEACHER_ASSISTANT")
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
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
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
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request)
    {
        $permissions = $request->request->get('permissions');

        $this->getSettingService()->set('assistant_permission', $permissions);

        return $this->getSettingService()->get('assistant_permission');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}