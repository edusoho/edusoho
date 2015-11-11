<?php
namespace Mooc\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\UserController as BaseUserController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class UserController extends BaseUserController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'roles'           => '',
            'keywordType'     => '',
            'keyword'         => '',
            'keywordUserType' => ''
        );

        if (empty($fields)) {
            $fields = array();
        }

        $conditions = array_merge($conditions, $fields);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userIds       = ArrayToolkit::column($users, 'id');
        $user_profiles = $this->getUserservice()->findUserProfilesByIds($userIds);

        foreach ($users as &$user) {
            $user['truename'] = $user_profiles[$user['id']]['truename'];
        }

        unset($user);
        $app = $this->getAppService()->findInstallApp("UserImporter");

        $showUserExport = false;

        if (!empty($app) && array_key_exists('version', $app)) {
            $showUserExport = version_compare($app['version'], "1.0.2", ">=");
        }

        $userIds  = ArrayToolkit::column($users, 'id');
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        return $this->render('TopxiaAdminBundle:User:index.html.twig', array(
            'users'          => $users,
            'paginator'      => $paginator,
            'profiles'       => $profiles,
            'showUserExport' => $showUserExport
        ));
    }
}
