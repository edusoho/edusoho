<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\JsonToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Content\Service\FileService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;

class SettingController extends BaseController
{
    public function securityAction(Request $request)
    {
        $security = $this->getSettingService()->get('security', []);
        $default = [
            'safe_iframe_domains' => [],
        ];
        $security = array_merge($default, $security);

        if ($request->isMethod('POST')) {
            $security = $request->request->all();

            $security['safe_iframe_domains'] = trim(str_replace(["\r\n", "\n", "\r"], ' ', $security['safe_iframe_domains']));
            $security['safe_iframe_domains'] = array_filter(explode(' ', $security['safe_iframe_domains']));

            $this->getSettingService()->set('security', $security);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/security/security.html.twig', [
            'security' => $security,
        ]);
    }

    public function ipBlacklistAction(Request $request)
    {
        $settingService = $this->getSettingService();

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();

            $purifiedBlackIps = trim(str_replace(["\r\n", "\n", "\r"], ' ', $data['blackListIps']));
            $purifiedWhiteIps = isset($data['whiteListIps']) ? $data['whiteListIps'] : null;
            $purifiedWhiteIps = trim(str_replace(["\r\n", "\n", "\r"], ' ', $purifiedWhiteIps));

            $logService = $this->getLogService();

            if (empty($purifiedBlackIps)) {
                $settingService->delete('blacklist_ip');

                $blackListIps['ips'] = [];
            } else {
                $blackListIps['ips'] = array_filter(explode(' ', $purifiedBlackIps));
                $settingService->set('blacklist_ip', $blackListIps);
            }

            if (empty($purifiedWhiteIps)) {
                $settingService->delete('whitelist_ip');

                $whiteListIps['ips'] = [];
            } else {
                $whiteListIps['ips'] = array_filter(explode(' ', $purifiedWhiteIps));
                $settingService->set('whitelist_ip', $whiteListIps);
            }

            $this->setFlashMessage('success', 'site.save.success');
        }

        $blackListIps = $settingService->get('blacklist_ip', []);
        $whiteListIps = $settingService->get('whitelist_ip', []);

        if (!empty($blackListIps)) {
            $default['ips'] = join("\n", $blackListIps['ips']);
            $blackListIps = array_merge($blackListIps, $default);
        } else {
            $blackListIps = [];
        }

        if (!empty($whiteListIps)) {
            $default['ips'] = join("\n", $whiteListIps['ips']);
            $whiteListIps = array_merge($whiteListIps, $default);
        } else {
            $whiteListIps = [];
        }

        return $this->render('admin-v2/system/security/ip-blacklist.html.twig', [
            'blackListIps' => $blackListIps,
            'whiteListIps' => $whiteListIps,
        ]);
    }

    public function postNumRulesAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $setting = $request->request->get('setting', []);
            $this->getSettingService()->set('post_num_rules', $setting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        $setting = $this->getSettingService()->get('post_num_rules', []);
        $setting = JsonToolkit::prettyPrint(json_encode($setting));

        return $this->render('admin-v2/system/security/post-num-rules.html.twig', [
            'setting' => $setting,
        ]);
    }

    public function logoUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $site = $this->getSettingService()->get('site', []);

        $oldFileId = empty($site['logo_file_id']) ? null : $site['logo_file_id'];
        $site['logo_file_id'] = $fileId;
        $site['logo'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $site['logo'] = ltrim($site['logo'], '/');

        $this->getSettingService()->set('site', $site);

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        $response = [
            'path' => $site['logo'],
            'url' => $this->container->get('assets.packages')->getUrl($site['logo']),
        ];

        return $this->createJsonResponse($response);
    }

    public function licensePictureUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $license = $this->getSettingService()->get('license', []);

        $oldFileId = empty($license['license_picture_file_id']) ? null : $license['license_picture_file_id'];
        $license['license_picture_file_id'] = $fileId;
        $license['license_picture'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $license['license_picture'] = ltrim($license['license_picture'], '/');

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        $response = [
            'path' => $license['license_picture'],
            'url' => $this->container->get('assets.packages')->getUrl($license['license_picture']),
        ];

        return $this->createJsonResponse($response);
    }

    public function logoRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('site');
        $setting['logo'] = '';

        $fileId = empty($setting['logo_file_id']) ? null : $setting['logo_file_id'];
        $setting['logo_file_id'] = '';

        $this->getSettingService()->set('site', $setting);

        if ($fileId) {
            $this->getFileService()->deleteFile($fileId);
        }

        return $this->createJsonResponse(true);
    }

    public function licensePictureRemoveAction(Request $resquest)
    {
        $setting = $this->getSettingService()->get('license');
        $setting['license_picture'] = '';

        $fileId = empty($setting['license_picture_file_id']) ? null : $setting['license_picture_file_id'];
        $setting['license_picture_file_id'] = '';

        $this->getSettingService()->set('license', $setting);

        if ($fileId) {
            $this->getFileService()->deleteFile($fileId);
        }

        return $this->createJsonResponse(true);
    }

    public function permitPictureUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $permit = $this->getSettingService()->get('license', []);

        $oldFileId = empty($permit['permit_picture_file_id']) ? null : $permit['permit_picture_file_id'];
        $permit['permit_picture_file_id'] = $fileId;
        $permit['permit_picture'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $permit['permit_picture'] = ltrim($permit['permit_picture'], '/');

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        $response = [
            'path' => $permit['permit_picture'],
            'url' => $this->container->get('assets.packages')->getUrl($permit['permit_picture']),
        ];

        return $this->createJsonResponse($response);
    }

    public function permitPictureRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('license');
        $setting['permit_picture'] = '';

        $fileId = empty($setting['permit_picture_file_id']) ? null : $setting['permit_picture_file_id'];
        $setting['permit_picture_file_id'] = '';

        $this->getSettingService()->set('license', $setting);

        if ($fileId) {
            $this->getFileService()->deleteFile($fileId);
        }

        return $this->createJsonResponse(true);
    }

    public function faviconUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $site = $this->getSettingService()->get('site', []);

        $oldFileId = empty($site['favicon_file_id']) ? null : $site['favicon_file_id'];
        $site['favicon_file_id'] = $fileId;
        $site['favicon'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $site['favicon'] = ltrim($site['favicon'], '/');

        $this->getSettingService()->set('site', $site);

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        //浏览器图标覆盖默认图标
        copy($this->getParameter('kernel.root_dir').'/../web/'.$site['favicon'], $this->getParameter('kernel.root_dir').'/../web/favicon.ico');

        $this->getLogService()->info('system', 'update_settings', '更新浏览器图标', ['favicon' => $site['favicon']]);

        $response = [
            'path' => $site['favicon'],
            'url' => $this->container->get('assets.packages')->getUrl($site['favicon']),
        ];

        return $this->createJsonResponse($response);
    }

    public function faviconRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('site');
        $setting['favicon'] = '';

        $fileId = empty($setting['favicon_file_id']) ? null : $setting['favicon_file_id'];
        $setting['favicon_file_id'] = '';

        $this->getSettingService()->set('site', $setting);

        if ($fileId) {
            $this->getFileService()->deleteFile($fileId);
        }

        return $this->createJsonResponse(true);
    }

    public function adminSyncAction(Request $request)
    {
        $currentUser = $this->getUser();
        $setting = $this->getSettingService()->get('user_partner', []);

        if (empty($setting['mode']) || !in_array($setting['mode'], ['phpwind', 'discuz'])) {
            return $this->createMessageResponse('info', '未开启用户中心，不能同步管理员帐号！');
        }

        $bind = $this->getUserService()->getUserBindByTypeAndUserId($setting['mode'], $currentUser['id']);

        if ($bind) {
            goto response;
        } else {
            $bind = null;
        }

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $partnerUser = $this->getAuthService()->checkPartnerLoginByNickname($data['nickname'], $data['password']);

            if (empty($partnerUser)) {
                $this->setFlashMessage('danger', 'site.incorrect.username_or_password');
                goto response;
            } else {
                $this->getUserService()->changeEmail($currentUser['id'], $partnerUser['email']);
                $this->getUserService()->changeNickname($currentUser['id'], $partnerUser['nickname']);
                $this->getUserService()->changePassword($currentUser['id'], $data['password']);
                $this->getUserService()->bindUser($setting['mode'], $partnerUser['id'], $currentUser['id'], null);
                $user = $this->getUserService()->getUser($currentUser['id']);
                $this->authenticateUser($user);

                $this->setFlashMessage('success', 'site.save.success');

                return $this->redirect($this->generateUrl('admin_v2_setting_user_center'));
            }
        }

        response:
        return $this->render('admin-v2/system/user-setting/admin-sync.html.twig', [
            'mode' => $setting['mode'],
            'bind' => $bind,
        ]);
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }
}
