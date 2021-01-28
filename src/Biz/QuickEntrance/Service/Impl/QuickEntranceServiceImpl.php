<?php

namespace Biz\QuickEntrance\Service\Impl;

use Biz\BaseService;
use Biz\QuickEntrance\Dao\QuickEntranceDao;
use Biz\QuickEntrance\Service\QuickEntranceService;
use Biz\Role\Util\PermissionBuilder;

class QuickEntranceServiceImpl extends BaseService implements QuickEntranceService
{
    private $defaultQuickEntranceCodes = array(
        'admin_v2_course_show',
        'admin_v2_block_manage',
        'admin_v2_classroom',
        'admin_v2_goods_order',
        'admin_v2_marketing_coupon',
        'admin_v2_user_show',
        'admin_v2_user_coin',
    );

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
        $navPermissions = array();
        $quickEntrances = array();
        foreach ($permissions['children'] as $permission) {
            if ('admin_v2' == $permission['code']) {
                $navPermissions = $permission['children'];
            }
        }

        foreach ($navPermissions as $navPermission) {
            if (!isset($navPermission['quick_entrance_icon_class'])) {
                continue;
            }

            $quickEntrances[$navPermission['code']] = array(
                'data' => $this->findEntrancesByNavPermission($navPermission),
                'title' => $this->trans($navPermission['name'], array(), 'menu'),
                'class' => $navPermission['quick_entrance_icon_class'],
            );
        }

        return $quickEntrances;
    }

    public function findSelectedEntrancesCodeByUserId($userId)
    {
        $userQuickEntrances = $this->getQuickEntranceDao()->getByUserId($userId);

        return empty($userQuickEntrances) ? $this->defaultQuickEntranceCodes : $userQuickEntrances['data'];
    }

    public function updateUserEntrances($userId, $entrances = array())
    {
        if (count($entrances) > self::QUICK_ENTRANCE_MAX_NUM) {
            throw $this->createInvalidArgumentException('Entrance invalid');
        }

        $userQuickEntrances = $this->getQuickEntranceDao()->getByUserId($userId);

        if (empty($userQuickEntrances)) {
            return $this->createUserEntrance($userId, $entrances);
        }

        $this->getQuickEntranceDao()->update($userQuickEntrances['id'], array('data' => $entrances));

        return $this->findEntrancesByUserId($userId);
    }

    public function createUserEntrance($userId, $entrances = array())
    {
        if (count($entrances) > self::QUICK_ENTRANCE_MAX_NUM) {
            throw $this->createInvalidArgumentException('Entrance invalid');
        }

        $this->getQuickEntranceDao()->create(array('userId' => $userId, 'data' => $entrances));

        return $this->findEntrancesByUserId($userId);
    }

    private function getEntrancesByCodes($codes)
    {
        $allQuickEntrances = $this->findAvailableEntrances();

        $entrances = array();
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
        $groups = empty($navPermission['children']) ? array() : $navPermission['children'];
        $quickEntrances = array();
        foreach ($groups as $group) {
            $quickEntrances = array_merge($quickEntrances, $this->findEntrancesByGroup($group));
        }

        return $quickEntrances;
    }

    private function findEntrancesByGroup($group)
    {
        $sideMenus = empty($group['children']) ? array() : $group['children'];
        $quickEntrances = array();
        foreach ($sideMenus as $sideMenu) {
            if (isset($sideMenu['quick_entrance_icon']) && $sideMenu['quick_entrance_icon'] && $this->getCurrentUser()->hasPermission($sideMenu['code'])) {
                $quickEntrances[] = array(
                    'code' => $sideMenu['code'],
                    'text' => $this->trans($sideMenu['name'], array(), 'menu'),
                    'icon' => $sideMenu['quick_entrance_icon'],
                    'target' => isset($sideMenu['target']) ? $sideMenu['target'] : '',
                );
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
