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

		if ($request->getMethod() == 'POST') {
			$profile = $request->request->get('profile');
			$this->getUserService()->updateUserProfile($user['id'], $profile);
			$this->setFlashMessage('success', '基础信息保存成功。');
			return $this->redirect($this->generateUrl('settings'));

		}

		$fields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();
		for($i=0;$i<count($fields);$i++){
			if(strstr($fields[$i]['fieldName'], "textField")) $fields[$i]['type']="text";
			if(strstr($fields[$i]['fieldName'], "varcharField")) $fields[$i]['type']="varchar";
			if(strstr($fields[$i]['fieldName'], "intField")) $fields[$i]['type']="int";
			if(strstr($fields[$i]['fieldName'], "floatField")) $fields[$i]['type']="float";
			if(strstr($fields[$i]['fieldName'], "dateField")) $fields[$i]['type']="date";
		}
		
		if (array_key_exists('idcard',$profile) && $profile['idcard']=="0") {
			$profile['idcard'] = "";
		}

		return $this->render('TopxiaWebBundle:Settings:profile.html.twig', array(
			'profile' => $profile,
			'fields'=>$fields,
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
			return $this->redirect($this->generateUrl('settings'));
		}
		return $this->render('TopxiaWebBundle:Settings:approval.html.twig',array(
		));
	}

	public function nicknameAction(Request $request)
	{
		$user = $this->getCurrentUser();
		
		$is_nickname = $this->getSettingService()->get('user_partner');

		if($is_nickname['nickname_enabled'] == 0){
			return $this->redirect($this->generateUrl('settings'));
		}

		if ($request->getMethod() == 'POST') {

			$nickname = $request->request->get('nickname');
			$this->getAuthService()->changeNickname($user['id'], $nickname);
			$this->setFlashMessage('success', '昵称修改成功！');
			return $this->redirect($this->generateUrl('settings'));
		}
		return $this->render('TopxiaWebBundle:Settings:nickname.html.twig',array(
		));
	}

	public function nicknameCheckAction(Request $request)
	{
		$nickname = $request->query->get('value');
		$currenUser = $this->getUserService()->getCurrentUser();

		if ($currenUser['nickname'] == $nickname){
			return $this->createJsonResponse(array('success' => true, 'message' => ''));
		}

		list ($result, $message) = $this->getAuthService()->checkUsername($nickname);
		if ($result == 'success'){
			$response = array('success' => true, 'message' => '');
		} else {
			$response = array('success' => false, 'message' => '昵称已存在');
		}
	
		return $this->createJsonResponse($response);
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

				$fileName = str_replace('.', '!', $file->getFilename());

				return $this->redirect($this->generateUrl('settings_avatar_crop', array(
					'file' => $fileName)
				));
			}
		}

		$hasPartnerAuth = $this->getAuthService()->hasPartnerAuth();
		if ($hasPartnerAuth) {
			$partnerAvatar = $this->getAuthService()->getPartnerAvatar($user['id'], 'big');
		} else {
			$partnerAvatar = null;
		}

		return $this->render('TopxiaWebBundle:Settings:avatar.html.twig', array(
			'form' => $form->createView(),
			'user' => $this->getUserService()->getUser($user['id']),
			'partnerAvatar' => $partnerAvatar,
		));
	}

	public function avatarCropAction(Request $request)
	{
		$currentUser = $this->getCurrentUser();
		$filename = $request->query->get('file');
		$filename = str_replace('!', '.', $filename);
		$filename = str_replace(array('..' , '/', '\\'), '', $filename);

		$pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

		if($request->getMethod() == 'POST') {
			$options = $request->request->all();
			$this->getUserService()->changeAvatar($currentUser['id'], $pictureFilePath, $options);
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
		$pictureUrl = 'tmp/' . $filename;

		return $this->render('TopxiaWebBundle:Settings:avatar-crop.html.twig', array(
			'pictureUrl' => $pictureUrl,
			'naturalSize' => $naturalSize,
			'scaledSize' => $scaledSize,
		));
	}

	public function avatarFetchPartnerAction(Request $request)
	{
		$currentUser = $this->getCurrentUser();
		if (!$this->getAuthService()->hasPartnerAuth()) {
			throw $this->createNotFoundException();
		}

		$url = $this->getAuthService()->getPartnerAvatar($currentUser['id'], 'big');
		if (empty($url)) {
			$this->setFlashMessage('danger', '获取论坛头像地址失败！');
			return $this->createJsonResponse(true);
		}

		$avatar = $this->fetchAvatar($url);
		if (empty($avatar)) {
			$this->setFlashMessage('danger', '获取论坛头像失败或超时，请重试！');
			return $this->createJsonResponse(true);
		}

		$avatarPath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $currentUser['id'] . '_' . time() . '.jpg';

		file_put_contents($avatarPath, $avatar);

		$this->getUserService()->changeAvatar($currentUser['id'], $avatarPath, array('x'=>0, 'y'=>0, 'width'=>200, 'height' => 200));

		return $this->createJsonResponse(true);
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
				if (!$this->getAuthService()->checkPassword($user['id'], $passwords['currentPassword'])) {
					$this->setFlashMessage('danger', '当前密码不正确，请重试！');
				} else {
					$this->getAuthService()->changePassword($user['id'], $passwords['currentPassword'], $passwords['newPassword']);
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
		$mailer = $this->getSettingService()->get('mailer', array());
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

				try {
					$this->sendEmail(
						$data['email'],
						"重设{$user['nickname']}在" . $this->setting('site.name', 'EDUSOHO') . "的电子邮箱",
						$this->renderView('TopxiaWebBundle:Settings:email-change.txt.twig', array(
							'user' => $user,
							'token' => $token,
						))
					);
					$this->setFlashMessage('success', "请到邮箱{$data['email']}中接收确认邮件，并点击确认邮件中的链接完成修改。");
				} catch (\Exception $e) {
					$this->setFlashMessage('danger', "邮箱变更确认邮件发送失败，请联系管理员。");
					$this->getLogService()->error('setting', 'email_change', '邮箱变更确认邮件发送失败:' . $e->getMessage());
				}

				return $this->redirect($this->generateUrl('settings_email'));
			}
		}

		return $this->render("TopxiaWebBundle:Settings:email.html.twig", array(
			'form' => $form->createView(),
			'mailer' =>$mailer
		));
	}

	public function emailVerifyAction()
	{
		$user = $this->getCurrentUser();
		$token = $this->getUserService()->makeToken('email-verify', $user['id'], strtotime('+1 day'), $user['email']);

		try {
			$this->sendEmail(
				$user['email'],
				"验证{$user['nickname']}在{$this->setting('site.name')}的电子邮箱",
				$this->renderView('TopxiaWebBundle:Settings:email-verify.txt.twig', array(
					'user' => $user,
					'token' => $token,
				))
			);
			$this->setFlashMessage('success',"请到邮箱{$user['email']}中接收验证邮件，并点击邮件中的链接完成验证。");
		} catch (\Exception $e) {
			$this->getLogService()->error('setting', 'email-verify', '邮箱验证邮件发送失败:' . $e->getMessage());
			$this->setFlashMessage('danger',"邮箱验证邮件发送失败，请联系管理员。");
		}


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
		$callback = $this->generateUrl('settings_binds_bind_callback', array('type' => $type), true);
		$settings = $this->setting('login_bind');
		$config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
		$client = OAuthClientFactory::create($type, $config);

		return $this->redirect($client->getAuthorizeUrl($callback));
	}

	public function bindCallbackAction (Request $request, $type)
	{
		$this->checkBindsName($type);
		$user = $this->getCurrentUser();
		if (empty($user)) {
			return $this->redirect($this->generateUrl('login'));
		}

		$bind = $this->getUserService()->getUserBindByTypeAndUserId($type, $user->id);
		if (! empty($bind)) {
			$this->setFlashMessage('danger', '您已经绑定了该第三方网站的帐号，不能重复绑定!');
			goto response;
		}

		$code = $request->query->get('code');
		if (empty($code)) {
			$this->setFlashMessage('danger', '您取消了授权/授权失败，请重试绑定!');
			goto response;
		}


		$callbackUrl = $this->generateUrl('settings_binds_bind_callback', array('type' => $type), true);
		try {
			$token = $this->createOAuthClient($type)->getAccessToken($code, $callbackUrl);
		} catch (\Exception $e) {
			$this->setFlashMessage('danger', '授权失败，请重试绑定!');
			goto response;
		}

		$bind = $this->getUserService()->getUserBindByTypeAndFromId($type, $token['userId']);
		if (!empty($bind)) {
			$this->setFlashMessage('danger', '该第三方帐号已经被其他帐号绑定，不能重复绑定!');
			goto response;
		}

		$this->getUserService()->bindUser($type, $token['userId'], $user['id'], $token);
		$this->setFlashMessage('success', '帐号绑定成功!');

		response:
		return $this->redirect($this->generateUrl('settings_binds'));

	}

	public function setupAction(Request $request)
	{
		$user = $this->getCurrentUser();

		if ($request->getMethod() == 'POST') {
			$data = $request->request->all();

			$this->getAuthService()->changeEmail($user['id'], null, $data['email']);
			$this->getAuthService()->changeNickname($user['id'], $data['nickname']);
			$user = $this->getUserService()->setupAccount($user['id']);
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
			list($result, $message) = $this->getAuthService()->checkUsername($nickname);
			if ($result == 'success') {
				$response = array('success' => true);
			} else {
				$response = array('success' => false, 'message' => $message);
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

	public function fetchAvatar($url)
	{

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);

		curl_setopt($curl, CURLOPT_URL, $url );

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

		$response = curl_exec($curl);

		curl_close($curl);

		return $response;
	}

	private function createOAuthClient($type)
	{
		$settings = $this->setting('login_bind');        

		if (empty($settings)) {
			throw new \RuntimeException('第三方登录系统参数尚未配置，请先配置。');
		}

		if (empty($settings) or !isset($settings[$type.'_enabled']) or empty($settings[$type.'_key']) or empty($settings[$type.'_secret'])) {
			throw new \RuntimeException("第三方登录({$type})系统参数尚未配置，请先配置。");
		}

		if (!$settings[$type.'_enabled']) {
			throw new \RuntimeException("第三方登录({$type})未开启");
		}

		$config = array('key' => $settings[$type.'_key'], 'secret' => $settings[$type.'_secret']);
		$client = OAuthClientFactory::create($type, $config);

		return $client;
	}

	private function getAuthService()
	{
		return $this->getServiceKernel()->createService('User.AuthService');
	}

	protected function getSettingService()
	{
		return $this->getServiceKernel()->createService('System.SettingService');
	}

	protected function getUserFieldService()
	{
		return $this->getServiceKernel()->createService('User.UserFieldService');
	}
}