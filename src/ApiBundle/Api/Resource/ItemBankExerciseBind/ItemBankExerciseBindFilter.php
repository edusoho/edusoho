<?php

namespace ApiBundle\Api\Resource\ItemBankExerciseBind;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseFilter;
use ApiBundle\Api\Resource\User\UserFilter;
use Biz\Role\Service\RoleService;

class ItemBankExerciseBindFilter extends Filter
{
    protected $publicFields = [
        'id', 'chapterExerciseNum', 'assessmentNum', 'bindType', 'bindId', 'createdTime', 'itemBankExercise', 'operateUser',
        'itemBankExerciseId', 'seq', 'updatedTime', 'roles',
    ];

    protected function publicFields(&$data)
    {
        $itemBankExerciseFilter = new ItemBankExerciseFilter();
        $itemBankExerciseFilter->setMode(Filter::PUBLIC_MODE);
        $itemBankExerciseFilter->filter($data['itemBankExercise']);
        $data['roles'] = implode(',', $this->convertRoles($data));
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::PUBLIC_MODE);
        $userFilter->filter($data['operateUser']);
    }

    protected function convertRoles(&$data)
    {
        $allRoles = $this->getAllRoles();
        if (isset($data['operateUser']['roles']) && is_array($data['operateUser']['roles'])) {
            foreach ($data['operateUser']['roles'] as &$role) {
                // 如果 `allRoles` 中有对应的中文描述，则替换
                if (isset($allRoles[$role])) {
                    $role = $allRoles[$role];
                }
            }
        }

        return $data['operateUser']['roles'];
    }

    protected function getAllRoles()
    {
        $roles = $this->getRoleService()->searchRoles([], 'created', 0, PHP_INT_MAX);

        $roleDicts = [];
        foreach ($roles as $role) {
            $roleDicts[$role['code']] = $role['name'];
        }

        return $roleDicts;
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }
}
