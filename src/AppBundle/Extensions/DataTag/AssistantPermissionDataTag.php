<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Course\Service\MemberService;

class AssistantPermissionDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取助教权限.
     *
     * @param array $arguments 参数
     *
     * @return array
     */
    public function getData(array $arguments)
    {
        $courseId = $arguments['courseId'];
        $user = $this->getCurrentUser();
        $isAssistant = $this->getMemberService()->isCourseAssistant($courseId, $user['id']);
        $assistantPermission = $this->getBiz()['assistant_permission'];
        $data = [
            'isAssistant' => true,
            'permissions' => $assistantPermission->getPermissions(),
        ];

        if (!empty(array_intersect(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], $user['roles']))) {
            $data['isAssistant'] = false;
        }

        if (!$isAssistant) {
            $data['isAssistant'] = false;
        }

        return $data;
    }

    protected function getBiz()
    {
        return $this->getServiceKernel()->getBiz();
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:MemberService');
    }
}
