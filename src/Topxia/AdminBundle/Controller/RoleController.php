<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\MenuBuilder;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RoleController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $fields = ArrayToolkit::filter($fields, array(
            'keyword'     => '',
            'keywordType' => ''
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

        return $this->render('TopxiaAdminBundle:System:role.html.twig', array(
            'roles'     => $roles,
            'paginator' => $paginator
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $params         = $request->request->all();
            $params['data'] = json_decode($params['data'], true);
            $role           = $this->getRoleService()->createRole($params);
            return $this->createJsonResponse(true);
        }

        $menuBuilder = new MenuBuilder();
        $res = $menuBuilder->getOriginPermissionTree();
        return $this->render('TopxiaAdminBundle:System:roles.html.twig', array('menus' => json_encode($res), 'model' => 'create'));
    }

    public function editAction(Request $request, $id)
    {
        $role = $this->getRoleService()->getRole($id);
        $menuBuilder = new MenuBuilder();
        $res = $menuBuilder->getOriginPermissionTree();

        if ('POST' == $request->getMethod()) {
            $params         = $request->request->all();
            $params['data'] = json_decode($params['data'], true);
            $role           = $this->getRoleService()->updateRole($id, $params);
            return $this->createJsonResponse(true);
        }

        if(!empty($role['data'])){
            foreach ($res as $key => &$permission) {
                if(in_array($permission['code'], $role['data'])) {
                    $permission['checked'] = true;
                }
            }
        }

        return $this->render('TopxiaAdminBundle:System:roles.html.twig', array('menus' => json_encode($res), 'model' => 'edit', 'role' => $role));
    }

    public function showAction(Request $request, $id)
    {
        $role = $this->getRoleService()->getRole($id);
        $menuBuilder = new MenuBuilder();

        $res  = $menuBuilder->getOriginMenus();

        foreach ($res as &$re) {
            if (in_array($re['code'], $role['data'])) {
                $re['checked'] = 'true';
            }

            $re['chkDisabled'] = 'true';
        }

        return $this->render('TopxiaAdminBundle:System:roles.html.twig', array('menus' => json_encode($res), 'model' => 'show', 'role' => $role));
    }

    protected function dataPrepare($position)
    {
        $configPaths = array();
        $rootDir     = realpath(__DIR__.'/../../../../');

        $configPaths[] = "{$rootDir}/src/Topxia/WebBundle/Resources/config/menus_{$position}.yml";
        $configPaths[] = "{$rootDir}/src/Topxia/AdminBundle/Resources/config/menus_{$position}.yml";

        $configPaths[] = "{$rootDir}/src/Classroom/ClassroomBundle/Resources/config/menus_{$position}.yml";

        $count = $this->getAppService()->findAppCount();
        $apps  = $this->getAppService()->findApps(0, $count);

        foreach ($apps as $app) {
            if ($app['type'] != 'plugin') {
                continue;
            }

            $code          = ucfirst($app['code']);
            $configPaths[] = "{$rootDir}/plugins/{$code}/{$code}Bundle/Resources/config/menus_{$position}.yml";
        }

        $configPaths[] = "{$rootDir}/src/Custom/WebBundle/Resources/config/menus_{$position}.yml";
        $configPaths[] = "{$rootDir}/src/Custom/AdminBundle/Resources/config/menus_{$position}.yml";

        $menus = array();

        foreach ($configPaths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $menu = Yaml::parse($path);

            if (empty($menu)) {
                continue;
            }

            $menus = array_merge($menus, $menu);
        }

        $i = 0;

        foreach ($menus as $key => &$menu) {
            $menu['id'] = $i++;

            if (!empty($menu['parent'])) {
                $menu['pId'] = $menus[$menu['parent']]['id'];
            } else {
                $menu['pId'] = 0;
            }

            $menu['code'] = $key;
        }

        $menus = ArrayToolkit::index($menus, 'id');

        return $menus;
    }

    public function checkNameAction(Request $request)
    {
        $name    = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getRoleService()->isRoleNameAvalieable($name, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '权限名称已存在');
        }

        return $this->createJsonResponse($response);
    }

    public function checkCodeAction(Request $request)
    {
        $code    = $request->query->get('value');
        $exclude = $request->query->get('exclude');

        $avaliable = $this->getRoleService()->isRoleCodeAvalieable($code, $exclude);

        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => 'code已存在');
        }

        return $this->createJsonResponse($response);
    }

    protected function getRoleService()
    {
        return $this->getServiceKernel()->createService('System.RoleService');
    }

    protected function getStatisticsService()
    {
        return $this->getServiceKernel()->createService('System.StatisticsService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getCashService()
    {
        return $this->getServiceKernel()->createService('Cash.CashService');
    }

    private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }
}
