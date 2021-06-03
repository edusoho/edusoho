<?php

namespace ApiBundle\Api\Resource\UserProfile;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use Biz\Role\Service\RoleService;
use Biz\User\Service\UserFieldService;
use Biz\User\UserException;

class UserProfile extends AbstractResource
{
    public function get(ApiRequest $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw UserException::NOTFOUND_USER();
        }

        if (1 == $user['destroyed']) {
            throw UserException::USER_IS_DESTROYED();
        }

        $user['roles'] = $this->getRoleService()->findRolesByCodes($user['roles']);

        $profile = $this->getUserService()->getUserProfile($userId);
        $profile['title'] = $user['title'];

        $fields = $this->getFields();
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::AUTHENTICATED_MODE);
        $userFilter->filters($user);

        return ['user' => $user, 'profile' => $profile, 'fields' => $fields];
    }

    protected function getFields()
    {
        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0; $i < count($fields); ++$i) {
            if (strstr($fields[$i]['fieldName'], 'textField')) {
                $fields[$i]['type'] = 'text';
            }

            if (strstr($fields[$i]['fieldName'], 'varcharField')) {
                $fields[$i]['type'] = 'varchar';
            }

            if (strstr($fields[$i]['fieldName'], 'intField')) {
                $fields[$i]['type'] = 'int';
            }

            if (strstr($fields[$i]['fieldName'], 'floatField')) {
                $fields[$i]['type'] = 'float';
            }

            if (strstr($fields[$i]['fieldName'], 'dateField')) {
                $fields[$i]['type'] = 'date';
            }
        }

        return $fields;
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->service('Role:RoleService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->service('User:UserFieldService');
    }

    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
