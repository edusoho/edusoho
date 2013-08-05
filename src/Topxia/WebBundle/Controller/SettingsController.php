<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Form\UserProfileType;
use Topxia\WebBundle\Form\TeacherProfileType;
use Topxia\Component\OAuthClient\OAuthClientFactory;

class SettingsController extends BaseController
{

	public function profileAction(Request $request)
	{
		$user = $this->getCurrentUser();

        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];

        $isTeacher = false;
        if (in_array('ROLE_TEACHER', $user['roles'])) {
            $form = $this->createForm(new TeacherProfileType(), $profile);
            $isTeacher = true;
        } else {
            $form = $this->createForm(new UserProfileType(), $profile);
        }

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $profile = $form->getData();
                $profile['birthday'] = empty($profile['birthday']) ? null : $profile['birthday'];

                $this->getUserService()->updateUserProfile($user['id'], $profile);
                $this->setFlashMessage('success', '基础信息保存成功。');

                return $this->redirect($this->generateUrl('settings'));
            }
        }

        return $this->render('TopxiaWebBundle:Settings:profile.html.twig', array(
            'form' => $form->createView(),
            'isTeacher' => $isTeacher,
        ));
	}

	public function avatarAction(Request $request)
	{
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $this->getUserService()->changeAvatar($user['id'], $data['avatar']);
                $this->setFlashMessage('success', '头像上传成功。');
            }
        }

		return $this->render('TopxiaWebBundle:Settings:avatar.html.twig', array(
            'form' => $form->createView(),
            'user' => $this->getUserService()->getUser($user['id']),
        ));
	}

	public function passwordAction(Request $request)
	{
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('currentPassword', 'password')
            ->add('newPassword', 'password')
            ->add('confirmPassword', 'password')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $passwords = $form->getData();
                if (!$this->getUserService()->verifyPassword($user['id'], $passwords['currentPassword'])) {
                	$this->setFlashMessage('danger', '当前密码不正确，请重试！');
                } else {
	                $this->getUserService()->changePassword($user['id'], $passwords['newPassword']);
	                $this->setFlashMessage('success', '密码修改成功。');
                }

                return $this->redirect($this->generateUrl('settings_password'));
            }
        }

		return $this->render('TopxiaWebBundle:Settings:password.html.twig', array(
			'form' => $form->createView()
		));
	}

	public function emailAction(Request $request)
	{
        $user = $this->getCurrentUser();

        $form = $this->createFormBuilder()
            ->add('password', 'password')
            ->add('email', 'text')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();

                $isPasswordOk = $this->getUserService()->verifyPassword($user['id'], $data['password']);

                if (!$isPasswordOk) {
                    $this->setFlashMessage('danger', '密码不正确，请重试。');
                    return $this->redirect($this->generateUrl('settings_email'));
                }

                $userOfNewEmail = $this->getUserService()->getUserByEmail($data['email']);
                if ($userOfNewEmail && $userOfNewEmail['id'] == $user['id']) {
                    $this->setFlashMessage('danger', '新邮箱，不能更当前邮箱一样。');
                    return $this->redirect($this->generateUrl('settings_email'));
                }

                if ($userOfNewEmail && $userOfNewEmail['id'] != $user['id']) {
                    $this->setFlashMessage('danger', '新邮箱已经被注册，请换一个试试。');
                    return $this->redirect($this->generateUrl('settings_email'));
                }

                $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'), $data['email']);

                $this->sendEmail(
                    $data['email'],
                    "重设{$user['nickname']}在" . $this->setting('site.name', 'EDUSOHO') . "的电子邮箱",
                    $this->renderView('TopxiaWebBundle:Settings:email-change.txt.twig', array(
                        'user' => $user,
                        'token' => $token,
                    ))
                );

                $this->setFlashMessage('success', "请到邮箱{$data['email']}中接收确认邮件，并点击确认邮件中的链接完成修改。");
  
                return $this->redirect($this->generateUrl('settings_email'));
            }
        }

        return $this->render("TopxiaWebBundle:Settings:email.html.twig", array(
            'form' => $form->createView()
        ));
	}

    public function emailVerifyAction()
    {
        $user = $this->getCurrentUser();
        $token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'), $user['email']);

        $this->sendEmail(
            $user['email'],
            "验证{$user['nickname']}在{$this->setting('site.name')}的电子邮箱",
            $this->renderView('TopxiaWebBundle:Settings:email-verify.txt.twig', array(
                'user' => $user,
                'token' => $token,
            ))
        );

        $this->setFlashMessage("请到邮箱{$user['email']}中接收验证邮件，并点击邮件中的链接完成验证。", 'success');

        return $this->createJsonResponse(true);
    }

	public function bindsAction(Request $request)
	{
        $user = $this->getCurrentUser();
        $binds = array(
            'weibo' => array(
                'type' => '新浪微博帐号' , 'image' => '/assets/img/social/weibo.png' , 'state' => null),
            'qq' => array(
                'type' => 'QQ帐号' , 'image' => '/assets/img/social/qzone.png' , 'state' => null),
            'renren' => array(
                'type' => '人人网帐号' , 'image' => '/assets/img/social/renren.gif' , 'state' => null)
        );
        $userBinds = $this->getUserService()->findBindsByUserId($user->id) ?  : array();

        foreach($userBinds as $userBind) {
            $binds[$userBind['type']]['state'] = 'bind';
        }
		return $this->render('TopxiaWebBundle:Settings:binds.html.twig', array('binds'=>$binds));
	}

    public function unBindAction(Request $request, $type){
        $user = $this->getCurrentUser();
        $this->checkBindsName($type);
        $userBinds = $this->getUserService()->unBindUserByTypeAndToId($type, $user->id);
        return $this->redirect($this->generateUrl('settings_binds'));
    }

    public function bindAction(Request $request, $type){
        $this->checkBindsName($type);
        $callback = $this->generateUrl('login_bind_callback', array('type' => $type), true);
        $settings = $this->setting('login_bind');
        $config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
        $client = OAuthClientFactory::create($type, $config);
        return $this->redirect($client->getAuthorizeUrl($callback));
    }

    private function checkBindsName($type) {
        $types = array('weibo', 'qq', 'renren');
        if (!in_array($type, $types)) {
            throw new NotFoundHttpException();
        }
    }

}