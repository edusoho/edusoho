<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CommonAdminController extends BaseController
{
    public function addCommonAdminAction(Request $request)
    {
        $data = $request->request->all();

        $user = $this->getUser();

        $admin['url'] = $data['url'];
        $admin['title'] = $data['title'];
        $admin['userId'] = $user['id'];

        if ($this->getUserCommonAdminService()->getCommonAdminByUserIdAndUrl($user['id'], $admin['url'])) {
            return $this->createJsonResponse('error');
        }

        $admin = $this->getUserCommonAdminService()->addCommonAdmin($admin);

        $commonAdmins = $this->getUserCommonAdminService()->findCommonAdminByUserId($user['id']);

        if (count($commonAdmins) > 10) {
            $deleteCommons = array_slice($commonAdmins, 10, count($commonAdmins) - 10);
            $ids = ArrayToolkit::column($deleteCommons, 'id');

            foreach ($ids as $key => $id) {
                $this->getUserCommonAdminService()->deleteCommonAdmin($id);
            }
        }

        return $this->render('admin/common-admin/li.html.twig', array(
            'admin' => $admin, ));
    }

    public function commonAdminAction(Request $request)
    {
        $user = $this->getUser();
        $url = $_SERVER['REQUEST_URI'];

        $isCollect = 0;

        if ($this->getUserCommonAdminService()->getCommonAdminByUserIdAndUrl($user['id'], $url)) {
            $isCollect = 1;
        }

        $admins = $this->getUserCommonAdminService()->findCommonAdminByUserId($user['id']);

        return $this->render('admin/common-admin/main.html.twig', array(
            'admins' => $admins,
            'isCollect' => $isCollect, ));
    }

    public function commonRemoveAction(Request $request, $id)
    {
        $url = $request->request->get('url');
        $this->getUserCommonAdminService()->deleteCommonAdmin($id);

        $user = $this->getUser();

        $isCollect = 0;

        if ($this->getUserCommonAdminService()->getCommonAdminByUserIdAndUrl($user['id'], $url)) {
            $isCollect = 1;
        }

        $admins = $this->getUserCommonAdminService()->findCommonAdminByUserId($user['id']);

        return $this->render('admin/common-admin/main.html.twig', array(
            'admins' => $admins,
            'isCollect' => $isCollect, ));
    }

    protected function getUserCommonAdminService()
    {
        return $this->createService('User:UserCommonAdminService');
    }
}
