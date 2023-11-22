<?php

namespace Biz\QuickEntrance\Service\Impl;

use Biz\BaseService;
use Biz\QuickEntrance\Dao\QuickEntranceDao;
use Biz\QuickEntrance\Service\QuickEntranceService;
use Biz\Role\Util\PermissionBuilder;

class QuickEntranceServiceImpl extends BaseService implements QuickEntranceService
{
    private $defaultQuickEntranceCodes = [
        'admin_v2_course_show',
        'admin_v2_block_manage',
        'admin_v2_classroom',
        'admin_v2_goods_order',
        'admin_v2_marketing_coupon',
        'admin_v2_user_show',
        'admin_v2_user_coin',
        'admin_v2_setting_operation',
    ];

    public function findEntrancesByUserId($userId)
    {
        $userQuickEntrances = $this->getQuickEntranceDao()->getByUserId($userId);

        if (empty($userQuickEntrances)) {
            return $this->getEntrancesByCodes($this->defaultQuickEntranceCodes);
        }

        return $this->getEntrancesByCodes($userQuickEntrances['data']);
    }

    public function findAvailableEntrances()
    {
        $permissions = PermissionBuilder::instance()->getUserPermissionTree()->toArray();
        $navPermissions = [];
        $quickEntrances = [];
        foreach ($permissions['children'] as $permission) {
            if ('admin_v2' == $permission['code']) {
                $navPermissions = $permission['children'];
            }
        }

        foreach ($navPermissions as $navPermission) {
            if (!isset($navPermission['quick_entrance_icon_class'])) {
                continue;
            }

            $quickEntrances[$navPermission['code']] = [
                'data' => $this->findEntrancesByNavPermission($navPermission),
                'title' => $this->trans($navPermission['name'], [], 'menu'),
                'class' => $navPermission['quick_entrance_icon_class'],
            ];
        }

        return $quickEntrances;
    }

    public function findSelectedEntrancesCodeByUserId($userId)
    {
        $userQuickEntrances = $this->getQuickEntranceDao()->getByUserId($userId);

        return empty($userQuickEntrances) ? $this->defaultQuickEntranceCodes : $userQuickEntrances['data'];
    }

    public function updateUserEntrances($userId, $entrances = [])
    {
        if (count($entrances) > self::QUICK_ENTRANCE_MAX_NUM) {
            throw $this->createInvalidArgumentException('Entrance invalid');
        }

        $userQuickEntrances = $this->getQuickEntranceDao()->getByUserId($userId);

        if (empty($userQuickEntrances)) {
            return $this->createUserEntrance($userId, $entrances);
        }

        $this->getQuickEntranceDao()->update($userQuickEntrances['id'], ['data' => $entrances]);

        return $this->findEntrancesByUserId($userId);
    }

    public function createUserEntrance($userId, $entrances = [])
    {
        if (count($entrances) > self::QUICK_ENTRANCE_MAX_NUM) {
            throw $this->createInvalidArgumentException('Entrance invalid');
        }

        $this->getQuickEntranceDao()->create(['userId' => $userId, 'data' => $entrances]);

        return $this->findEntrancesByUserId($userId);
    }

    private function getEntrancesByCodes($codes)
    {
        $allQuickEntrances = $this->findAvailableEntrances();

        $entrances = [];
        foreach ($allQuickEntrances as $item) {
            if (empty($item['data'])) {
                continue;
            }

            array_map(function ($entrance) use ($codes, $item, &$entrances) {
                if (in_array($entrance['code'], $codes, true)) {
                    $entrance['class'] = $item['class'];
                    $entrances[] = $entrance;
                }
            }, $item['data']);
        }

        return $entrances;
    }

    private function findEntrancesByNavPermission($navPermission)
    {
        $groups = empty($navPermission['children']) ? [] : $navPermission['children'];
        $quickEntrances = [];
        foreach ($groups as $group) {
            $quickEntrances = array_merge($quickEntrances, $this->findEntrancesByGroup($group));
        }

        return $quickEntrances;
    }

    private function findEntrancesByGroup($group)
    {
        $sideMenus = empty($group['children']) ? [] : $group['children'];
        $quickEntrances = [];
        foreach ($sideMenus as $sideMenu) {
            if (isset($sideMenu['quick_entrance_icon']) && $sideMenu['quick_entrance_icon'] && $this->getCurrentUser()->hasPermission($sideMenu['code'])) {
                $quickEntrances[] = [
                    'code' => $sideMenu['code'],
                    'text' => $this->trans($sideMenu['name'], [], 'menu'),
                    'icon' => $sideMenu['quick_entrance_icon'],
                    'target' => isset($sideMenu['target']) ? $sideMenu['target'] : '',
                ];
            }
        }

        return $quickEntrances;
    }

    /**
     * @return QuickEntranceDao
     */
    private function getQuickEntranceDao()
    {
        return $this->createDao('QuickEntrance:QuickEntranceDao');
    }
}
