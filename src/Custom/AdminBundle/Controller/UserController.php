<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/21
 * Time: 09:42
 */

namespace Custom\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\UserController as BaseUserController;

class UserController extends BaseUserController
{
    public function editAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];
        if ($request->getMethod() == 'POST') {
            $profile = $request->request->all();
            $fields['staffNo'] = !empty($profile['staffNo'])?$profile['staffNo'] : '';
            if (!( (strlen($user['verifiedMobile']) > 0) && isset($profile['mobile']) )) {
                $profile = $this->getUserService()->updateUserProfile($user['id'], $profile);
                $this->getUserService()->updateUserStaffNo($fields['staffNo'], $user['id']);
                $this->getLogService()->info('user', 'edit', "管理员编辑用户资料 {$user['nickname']} (#{$user['id']})", $profile);
            } else {
                $this->setFlashMessage('danger', '用户已绑定的手机不能修改。');
            }

            return $this->redirect($this->generateUrl('admin_user'));
        }

        $fields=$this->getFields();

        return $this->render('TopxiaAdminBundle:User:edit-modal.html.twig', array(
            'user' => $user,
            'profile'=>$profile,
            'fields'=>$fields,
        ));
    }
}