<?php

namespace Biz\QuickEntrance\Service\Impl;

use AppBundle\Common\ArrayToolkit;
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

    public function getEntrancesByUserId($userId)
    {
        $userQuickEntrances = $this->getQuickEntranceDao()->getByUserId($userId);

        $codes = empty($userQuickEntrances['data']) ? $this->defaultQuickEntranceCodes : $userQuickEntrances['data'];

        return $this->getEntrancesByCodes($codes);
    }

    public function getAllEntrances($userId = 0)
    {
        if ($userId) {
            $userQuickEntrances = $this->getQuickEntranceDao()->getByUserId($userId);
        }

        $userEntranceCodes = empty($userQuickEntrances['data']) ? $this->defaultQuickEntranceCodes : $userQuickEntrances['data'];

        $permissions = PermissionBuilder::instance()->getUserPermissionTree();

        $permissions = $permissions->toArray();

        $modules = array();

        foreach ($permissions['children'] as $permission) {
            if ('admin_v2' == $permission['code']) {
                $modules = $permission['children'];
            }
        }

        $quickEntrances = array();
        foreach ($modules as $module) {
            if (!isset($module['class'])) {
                continue;
            }

            $quickEntrances[$module['code']] = array(
                'data' => $this->getEntrancesArray($module, array(), $userEntranceCodes),
                'title' => $this->trans($module['name'], array(), 'menu'),
                'class' => isset($module['class']) ? $module['class'] : '',
            );
        }

        return $quickEntrances;
    }

    public function updateUserEntrances($userId, $fields)
    {
        $fields = ArrayToolkit::filter($fields, array('data' => array()));

        if (count($fields['data']) > 7) {
            throw $this->createInvalidArgumentException('Entrance data invalid.');
        }

        $userQuickEntrances = $this->getQuickEntranceDao()->getByUserId($userId);

        if (empty($userQuickEntrances)) {
            $fields['userId'] = $userId;
            $this->createUserEntrance($fields);
        } else {
            $this->getQuickEntranceDao()->update($userQuickEntrances['id'], $fields);
        }

        return $this->getEntrancesByUserId($userId);
    }

    public function createUserEntrance($fields)
    {
        $fields = ArrayToolkit::filter($fields, array('userId' => 0, 'data' => array()));

        if (!ArrayToolkit::requireds($fields, array('userId', 'data')) || count($fields['data']) > 7) {
            throw $this->createInvalidArgumentException('Fields invalid');
        }

        $this->getQuickEntranceDao()->create($fields);

        return $this->getEntrancesByUserId($fields['userId']);
    }

    private function getEntrancesByCodes($codes)
    {
        $allQuickEntrances = $this->getAllEntrances();

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

    private function getEntrancesArray($module, $moduleQuickEntrances, $userEntranceCodes)
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
                    'checked' => in_array($module['code'], $userEntranceCodes) ? true : false,
                ),
            );
        } else {
            $moduleQuickEntrances = array();
        }

        if (isset($module['children'])) {
            foreach ($module['children'] as $child) {
                $moduleQuickEntrances = array_merge($moduleQuickEntrances, $this->getEntrancesArray($child, $moduleQuickEntrances, $userEntranceCodes));
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
