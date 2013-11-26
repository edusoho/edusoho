<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\WebBundle\Form\UserProfileType;
use Topxia\WebBundle\Form\TeacherProfileType;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class SettingsController extends BaseController
{

	public function profileAction(Request $request)
	{
		$user = $this->getCurrentUser();

        $profile = $this->getUserService()->getUserProfile($user['id']);
        $profile['title'] = $user['title'];

        $form = $this->createForm(new UserProfileType(), $profile);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $profile = $form->getData();
                $this->getUserService()->updateUserProfile($user['id'], $profile);
                $this->setFlashMessage('success', '基础信息保存成功。');
                return $this->redirect($this->generateUrl('settings'));
            }
        }

        return $this->render('TopxiaWebBundle:Settings:profile.html.twig', array(
            'form' => $form->createView()
        ));
	}

    public function approvalSubmitAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $faceImg = $request->files->get('faceImg');
            $backImg = $request->files->get('backImg');
            
            if (!FileToolkit::isImageFile($backImg) || !FileToolkit::isImageFile($faceImg) ) {
                return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, bmp,gif, png格式的文件。');
            }

            $directory = $this->container->getParameter('topxia.upload.private_directory') . '/approval';
            $this->getUserService()->applyUserApproval($user['id'], $request->request->all(), $faceImg, $backImg, $directory);
            $this->setFlashMessage('success', '实名认证提交成功！');
            return $this->redirect($this->generateUrl('setting_approval_status'));
        }
        return $this->render('TopxiaWebBundle:Settings:approval.html.twig',array(
        ));
    }

    public function approvalStatusAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Settings:approval-status-info.html.twig');
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
                $file = $data['avatar'];

                if (!FileToolkit::isImageFile($file)) {
                    return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
                }

                $filenamePrefix = "user_{$user['id']}_";
                $hash = substr(md5($filenamePrefix . time()), -8);
                $ext = $file->getClientOriginalExtension();
                $filename = $filenamePrefix . $hash . '.' . $ext;

                $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
                $file = $file->move($directory, $filename);

                return $this->redirect($this->generateUrl('settings_avatar_crop', array(
                    'userId' => $user['id'],
                    'file' => $file->getFilename())
                ));
            }
        }

		return $this->render('TopxiaWebBundle:Settings:avatar.html.twig', array(
            'form' => $form->createView(),
            'user' => $this->getUserService()->getUser($user['id']),
        ));
	}

    public function avatarCropAction(Request $request, $userId)
    {
        $filename = $request->query->get('file');
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);

        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        if($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $this->getUserService()->changeAvatar($userId, $pictureFilePath, $options);
            return $this->redirect($this->generateUrl('settings_avatar'));
        }

        try {
            $imagine = new Imagine();
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(270)->heighten(270);
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;

        return $this->render('TopxiaWebBundle:Settings:avatar-crop.html.twig', array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

	public function passwordAction(Request $request)
	{
        $user = $this->getCurrentUser();

        if (empty($user['setup'])) {
            return $this->redirect($this->generateUrl('settings_setup'));
        }

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

        if (empty($user['setup'])) {
            return $this->redirect($this->generateUrl('settings_setup'));
        }

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
                    $this->setFlashMessage('danger', '新邮箱，不能跟当前邮箱一样。');
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

        $this->setFlashMessage('success',"请到邮箱{$user['email']}中接收验证邮件，并点击邮件中的链接完成验证。");

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

    public function unBindAction(Request $request, $type)
    {
        $user = $this->getCurrentUser();
        $this->checkBindsName($type);
        $userBinds = $this->getUserService()->unBindUserByTypeAndToId($type, $user->id);
        return $this->redirect($this->generateUrl('settings_binds'));
    }

    public function bindAction(Request $request, $type)
    {
        $this->checkBindsName($type);
        $callback = $this->generateUrl('login_bind_callback', array('type' => $type), true);
        $settings = $this->setting('login_bind');
        $config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
        $client = OAuthClientFactory::create($type, $config);

        return $this->redirect($client->getAuthorizeUrl($callback));
    }

    public function setupAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $user = $this->getUserService()->setupAccount($user['id'], $request->request->all());
            $this->authenticateUser($user);
            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaWebBundle:Settings:setup.html.twig');
    }

    public function setupCheckNicknameAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $nickname = $request->query->get('value');

        if ($nickname == $user['nickname']) {
            $response = array('success' => true);
        } else {
            if ($this->getUserService()->isNicknameAvaliable($nickname)) {
                $response = array('success' => true);
            } else {
                $response = array('success' => false, 'message' => '该昵称已经被占用了');
            }
        }

        return $this->createJsonResponse($response);
    }

    private function checkBindsName($type) 
    {
        $types = array('weibo', 'qq', 'renren');
        if (!in_array($type, $types)) {
            throw new NotFoundHttpException();
        }
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }
}