<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 15:41
 */

namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\FileToolkit;
use Topxia\WebBundle\Controller\SettingsController as BaseSettingsController;

class SettingsController extends BaseSettingsController
{
    public function profileAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $profile = $this->getUserService()->getUserProfile($user['id']);

        $profile['title'] = $user['title'];

        if ($request->getMethod() == 'POST') {
            $profile = $request->request->get('profile');

            if (!((strlen($user['verifiedMobile']) > 0) && (isset($profile['mobile'])))){
                $this->getUserService()->updateUserStaffNo($request->request->get('staffNo'), $user->id);
                $this->getUserService()->updateUserProfile($user['id'], $profile);
                $this->setFlashMessage('success', '基础信息保存成功。');
            } else {
                $this->setFlashMessage('danger', '不能修改已绑定的手机。');
        }
        return $this->redirect($this->generateUrl('settings'));

        }

        $fields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();


        if (array_key_exists('idcard',$profile) && $profile['idcard']=="0") {
            $profile['idcard'] = "";
        }

        $fromCourse = $request->query->get('fromCourse');

        return $this->render('TopxiaWebBundle:Settings:profile.html.twig', array(
            'profile' => $profile,
            'fields'=>$fields,
            'fromCourse' => $fromCourse,
            'user' => $user
        ));
    }

}