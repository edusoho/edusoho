<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\AppService;
use Biz\Role\Service\RoleService;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class RoleController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $fields = ArrayToolkit::filter($fields, array(
            'keyword' => '',
            'keywordType' => '',
        ));
        $conditons = array();

        if (isset($fields['keywordType']) && !empty($fields['keywordType'])) {
            $conditons[$fields['keywordType']] = $fields['keyword'];
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getRoleService()->searchRolesCount($conditons),
            30
        );

        $roles = $this->getRoleService()->searchRoles(
            $conditons,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($roles, 'createdUserId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        return $this->render('admin-v2/system/role/index.html.twig', array(
            'roles' => $roles,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $params = $request->request->all();
            $params['data_v2'] = json_decode($params['data'], true);
            $params['data'] = json_decode($params['permissions'], true);
            $params['data'] = $this->getRoleService()->getAllParentPermissions($params['data']);
            $this->getRoleService()->createRole($params);

            return $this->createJsonResponse(true);
        }

        $tree = PermissionBuilder::instance()->getOriginPermissionTree();
        $res = $tree->toArray();
        $children = $this->getRoleService()->rolesTreeTrans($res['children'], 'adminV2');
        $children = $this->getRoleService()->filterRoleTree($children);

        return $this->render('admin-v2/system/role/role-modal.html.twig', array(
            'menus' => json_encode($children),
            'model' => 'create',
        ));
    }

    public function editAction(Request $request, $id)
    {
        $role = $this->getRoleService()->getRole($id);

        if ('POST' == $request->getMethod()) {
            $params = $request->request->all();
            $params['data_v2'] = json_decode($params['data'], true);
            $params['data'] = json_decode($params['permissions'], true);
            $params['data'] = $this->getRoleService()->getAllParentPermissions($params['data']);
            $this->getRoleService()->updateRole($id, $params);

            return $this->createJsonResponse(true);
        }

        $tree = PermissionBuilder::instance()->getOriginPermissionTree();
        if (!empty($role['data_v2'])) {
            $tree->each(function (&$tree) use ($role) {
                if (in_array($tree->data['code'], $role['data_v2'])) {
                    $tree->data['checked'] = true;
                }
            });
        }

        $res = $tree->toArray();
        $children = $this->getRoleService()->rolesTreeTrans($res['children'], 'adminV2');
        $children = $this->getRoleService()->filterRoleTree($children);

        return $this->render('admin-v2/system/role/role-modal.html.twig', array(
            'menus' => json_encode($children),
            'model' => 'edit',
            'role' => $role,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getRoleService()->deleteRole($id);

        return $this->createJsonResponse(array('result' => true));
    }

    public function showAction(Request $request, $id)
    {
        $role = $this->getRoleService()->getRole($id);
        $tree = PermissionBuilder::instance()->getOriginPermissionTree();

        $tree->each(function (&$tree) use ($role) {
            if (in_array($tree->data['code'], $role['data_v2'])) {
                $tree->data['checked'] = true;
            }

            $tree->data['chkDisabled'] = 'true';
        });

        $res = $tree->toArray();

        $children = $this->getRoleService()->rolesTreeTrans($res['children'], 'adminV2');
        $children = $this->getRoleService()->filterRoleTree($children);

        return $this->render('admin-v2/system/role/role-modal.html.twig', array(
            'menus' => json_encode($children),
            'model' => 'show',
            'role' => $role,
        ));
    }

    public function checkNameAction(Request $request)
    {
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getRoleService()->isRoleNameAvalieable($name, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '角色名称已存在');
        }

        return $this->createJsonResponse($response);
    }

    public function checkCodeAction(Request $request)
    {
        $code = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getRoleService()->isRoleCodeAvalieable($code, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '编码已存在');
        }

        return $this->createJsonResponse($response);
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
