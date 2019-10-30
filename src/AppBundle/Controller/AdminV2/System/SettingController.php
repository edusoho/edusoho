<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\JsonToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\FileToolkit;
use Biz\Content\Service\FileService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class SettingController extends BaseController
{
    public function securityAction(Request $request)
    {
        $security = $this->getSettingService()->get('security', array());
        $default = array(
            'safe_iframe_domains' => array(),
        );
        $security = array_merge($default, $security);

        if ($request->isMethod('POST')) {
            $security = $request->request->all();

            $security['safe_iframe_domains'] = trim(str_replace(array("\r\n", "\n", "\r"), ' ', $security['safe_iframe_domains']));
            $security['safe_iframe_domains'] = array_filter(explode(' ', $security['safe_iframe_domains']));

            $this->getSettingService()->set('security', $security);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/security/security.html.twig', array(
            'security' => $security,
        ));
    }

    public function ipBlacklistAction(Request $request)
    {
        $settingService = $this->getSettingService();

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();

            $purifiedBlackIps = trim(str_replace(array("\r\n", "\n", "\r"), ' ', $data['blackListIps']));
            $purifiedWhiteIps = isset($data['whiteListIps']) ? $data['whiteListIps'] : null;
            $purifiedWhiteIps = trim(str_replace(array("\r\n", "\n", "\r"), ' ', $purifiedWhiteIps));

            $logService = $this->getLogService();

            if (empty($purifiedBlackIps)) {
                $settingService->delete('blacklist_ip');

                $blackListIps['ips'] = array();
            } else {
                $blackListIps['ips'] = array_filter(explode(' ', $purifiedBlackIps));
                $settingService->set('blacklist_ip', $blackListIps);
            }

            if (empty($purifiedWhiteIps)) {
                $settingService->delete('whitelist_ip');

                $whiteListIps['ips'] = array();
            } else {
                $whiteListIps['ips'] = array_filter(explode(' ', $purifiedWhiteIps));
                $settingService->set('whitelist_ip', $whiteListIps);
            }

            $this->setFlashMessage('success', 'site.save.success');
        }

        $blackListIps = $settingService->get('blacklist_ip', array());
        $whiteListIps = $settingService->get('whitelist_ip', array());

        if (!empty($blackListIps)) {
            $default['ips'] = join("\n", $blackListIps['ips']);
            $blackListIps = array_merge($blackListIps, $default);
        } else {
            $blackListIps = array();
        }

        if (!empty($whiteListIps)) {
            $default['ips'] = join("\n", $whiteListIps['ips']);
            $whiteListIps = array_merge($whiteListIps, $default);
        } else {
            $whiteListIps = array();
        }

        return $this->render('admin-v2/system/security/ip-blacklist.html.twig', array(
            'blackListIps' => $blackListIps,
            'whiteListIps' => $whiteListIps,
        ));
    }

    public function postNumRulesAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $setting = $request->request->get('setting', array());
            $this->getSettingService()->set('post_num_rules', $setting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        $setting = $this->getSettingService()->get('post_num_rules', array());
        $setting = JsonToolkit::prettyPrint(json_encode($setting));

        return $this->render('admin-v2/system/security/post-num-rules.html.twig', array(
            'setting' => $setting,
        ));
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

        $site = $this->getSettingService()->get('site', array());

        $oldFileId = empty($site['logo_file_id']) ? null : $site['logo_file_id'];
        $site['logo_file_id'] = $fileId;
        $site['logo'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $site['logo'] = ltrim($site['logo'], '/');

        $this->getSettingService()->set('site', $site);

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        $response = array(
            'path' => $site['logo'],
            'url' => $this->container->get('templating.helper.assets')->getUrl($site['logo']),
        );

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

    public function faviconUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $site = $this->getSettingService()->get('site', array());

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

        $this->getLogService()->info('system', 'update_settings', '更新浏览器图标', array('favicon' => $site['favicon']));

        $response = array(
            'path' => $site['favicon'],
            'url' => $this->container->get('templating.helper.assets')->getUrl($site['favicon']),
        );

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
}
