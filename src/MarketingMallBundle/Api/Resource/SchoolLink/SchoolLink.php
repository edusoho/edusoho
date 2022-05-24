<?php

namespace MarketingMallBundle\Api\Resource\SchoolLink;

use ApiBundle\Api\ApiRequest;
use Biz\Role\Util\PermissionBuilder;
use MarketingMallBundle\Api\Resource\BaseResource;

class SchoolLink extends BaseResource
{
    public function search(ApiRequest $request)
    {
        $currentUser = $this->getBiz()['user'];
        $permissions = PermissionBuilder::instance()->getSubPermissions('admin_v2', null);
        $urlArray = [];
        $urlArray['permission_urls'][] = [
            'name' => '概况',
            'url' => $this->generateUrl('admin_v2', [], 0),
            'is_show' => true,
        ];
        foreach ($permissions as $permission) {
            if (!$permission['visible'] && 'admin_v2_developer_module' != $permission['code']) {
                $tabMenu = $this->container->get('permission.twig.permission_extension')->getFirstChild($this->container->get('permission.twig.permission_extension')->getFirstChild($this->container->get('permission.twig.permission_extension')->getFirstChild($permission, true, false), true, false), true, false);
                $name = $this->trans($permission['name']);
                $path = !empty($tabMenu['router_name']) ? $this->generateUrl($tabMenu['router_name'], [], 0) : '';
                $urlArray['permission_urls'][] = [
                    'name' => $this->trans($name, [], 'menu'),
                    'url' => $path,
                    'is_show' => true,
                ];
            }
        }

        $urlArray['cloud_url'] = [
            'name' => '云市场',
            'url' => $this->generateUrl('admin_v2_app_center', [], 0),
            'is_show' => $this->isShowCloud() && $currentUser->hasPermission('admin_v2_app_center'),
        ];

        $urlArray['admin_url'] = [
            'name' => $this->isShowEdusoho() ? 'EduSoho管理后台' : '管理后台',
            'url' => $this->generateUrl('admin_v2', [], 0),
            'is_show' => true,
        ];

        $urlArray['homepage_url'] = [
            'name' => '访问首页',
            'url' => $this->generateUrl('homepage', [], 0),
            'is_show' => true,
        ];

        //不管有无权限，都展示创建课程、班级按钮
        $urlArray['create_course_url'] = [
            'name' => '创建课程',
            'url' => $this->generateUrl('course_set_manage_create', [], 0),
            'is_show' => true,
        ];

        $urlArray['create_classroom_url'] = [
            'name' => '创建班级',
            'url' => $this->generateUrl('admin_v2_classroom_create', [], 0),
            'is_show' => true,
        ];

        return $urlArray;
    }

    protected function isShowCloud()
    {
        $copyright = $this->getSettingService()->get('copyright.thirdCopyright', false);
        $isWithoutNetWork = $this->getSettingService()->get('developer.without_network', false);

        return !(1 == $copyright) && !$isWithoutNetWork;
    }

    protected function isShowEdusoho()
    {
        $copyright = $this->getSettingService()->get('copyright', []);

        return !$copyright['owned'] || (2 == !$copyright['thirdCopyright']);
    }

    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
