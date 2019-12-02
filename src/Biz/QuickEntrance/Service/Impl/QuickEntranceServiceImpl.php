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
                'data' => $this->getEntrancesByPermission($navPermission),
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

    private function getEntrancesByPermission($module)
    {
        if (isset($module['quick_entrance_icon']) && $module['quick_entrance_icon'] && $this->getCurrentUser()->hasPermission($module['code'])) {
            $params = isset($module['router_params']) ? $module['router_params'] : array();

            global $kernel;
            $moduleQuickEntrances = array(
                array(
                    'code' => $module['code'],
                    'text' => $this->trans($module['name'], array(), 'menu'),
                    'icon' => $module['quick_entrance_icon'],
                    'link' => $kernel->getContainer()->get('router')->generate($module['router_name'], $params),
                    'target' => isset($module['target']) ? $module['target'] : '',
                ),
            );
        } else {
            $moduleQuickEntrances = array();
        }

        if (isset($module['children'])) {
            foreach ($module['children'] as $child) {
                $moduleQuickEntrances = array_merge($moduleQuickEntrances, $this->getEntrancesByPermission($child));
            }
        }

        return $moduleQuickEntrances;
    }

    /**
     * @return QuickEntranceDao
     */
    private function getQuickEntranceDao()
    {
        return $this->createDao('QuickEntrance:QuickEntranceDao');
    }
}
