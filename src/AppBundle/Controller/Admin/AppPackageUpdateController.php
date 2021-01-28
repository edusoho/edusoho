<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class AppPackageUpdateController extends BaseController
{
    public function modalAction(Request $request, $id)
    {
        $package = $this->getAppService()->getCenterPackageInfo($id);

        return $this->render('admin/app-package-update/modal.html.twig', array(
            'package' => $package,
        ));
    }

    public function checkEnvironmentAction(Request $request, $id)
    {
        $settings = $this->getSettingService()->get('storage', array());

        $api = CloudAPIFactory::create('root');
        // 当网校key不可用(被封禁)时，返回为{"error" => "xxx"}
        $info = $api->get('/me');

        if (empty($info['accessKey']) || empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $errors = array(sprintf('您尚未申请云平台授权码，<a href="%s">请先申请授权码</a>。', $this->generateUrl('admin_setting_cloud_key_update')));
        } else {
            $errors = $this->getAppService()->checkEnvironmentForPackageUpdate($id);
        }

        return $this->createResponseWithErrors($errors);
    }

    public function checkDependsAction(Request $request, $id)
    {
        $errors = $this->getAppService()->checkDependsForPackageUpdate($id);

        return $this->createResponseWithErrors($errors);
    }

    public function backupFileAction(Request $request, $id)
    {
        $errors = $this->getAppService()->backupFileForPackageUpdate($id);

        return $this->createResponseWithErrors($errors);
    }

    public function backupDbAction(Request $request, $id)
    {
        $errors = $this->getAppService()->backupDbForPackageUpdate($id);

        return $this->createResponseWithErrors($errors);
    }

    public function downloadAndExtractAction(Request $request, $id)
    {
        $errors = $this->getAppService()->downloadPackageForUpdate($id);

        return $this->createResponseWithErrors($errors);
    }

    public function checkDownloadAndExtractAction(Request $request, $id)
    {
        $errors = $this->getAppService()->checkDownloadPackageForUpdate($id);

        return $this->createResponseWithErrors($errors);
    }

    public function checklastErrorAction(Request $request, $id)
    {
        $result = $this->getAppService()->hasLastErrorForPackageUpdate($id);

        return $this->createJsonResponse($result);
    }

    public function beginUpgradeAction(Request $request, $id)
    {
        $index = $request->query->get('index', 0);
        $errors = $this->getAppService()->beginPackageUpdate($id, $request->query->get('type'), $index);
        if (empty($errors)) {
            echo json_encode(array('status' => 'ok'));
            exit;
        }

        if (isset($errors['index'])) {
            echo json_encode($errors);
            exit;
        }

        return $this->createResponseWithErrors($errors);
    }

    public function checkNewestAction($code)
    {
        try {
            if (empty($code)) {
                $errors[] = '参数缺失,更新应用包失败';

                return $this->createJsonResponse(array(
                    'status' => 'error',
                    'errors' => $errors,
                ));
            }

            $apps = $this->getAppService()->checkAppUpgrades();

            if (empty($apps)) {
                return $this->createJsonResponse(array(
                    'isUpgrade' => false,
                ));
            }

            $apps = ArrayToolkit::index($apps, 'code');

            if (empty($apps[$code])) {
                return $this->createJsonResponse(array(
                    'isUpgrade' => false,
                ));
            }

            if (empty($apps[$code]['package']['id']) || empty($apps[$code]['package']['toVersion'])) {
                $errors[] = '获取当前最新应用包信息失败';

                return $this->createJsonResponse(array(
                    'status' => 'error',
                    'errors' => $errors,
                ));
            }

            $result = array(
                'isUpgrade' => true,
                'packageId' => $apps[$code]['package']['id'],
                'toVersion' => $apps[$code]['package']['toVersion'],
                'urls' => array(
                    'checkDependsUrl' => $this->generateUrl('admin_app_package_update_check_depends', array('id' => $apps[$code]['package']['id'])),
                    'backupFileUrl' => $this->generateUrl('admin_app_package_update_backup_file', array('id' => $apps[$code]['package']['id'])),
                    'backupDbUrl' => $this->generateUrl('admin_app_package_update_backup_db', array('id' => $apps[$code]['package']['id'])),
                    'checkDownloadExtractUrl' => $this->generateUrl('admin_app_package_update_check_download_and_extract', array('id' => $apps[$code]['package']['id'])),
                    'downloadExtractUrl' => $this->generateUrl('admin_app_package_update_download_and_extract', array('id' => $apps[$code]['package']['id'])),
                    'beginUpgradeUrl' => $this->generateUrl('admin_app_package_update_begin_upgrade', array('id' => $apps[$code]['package']['id'], 'type' => 'upgrade')),
                    'checkNewestUrl' => $this->generateUrl('admin_app_package_update_check_newest', array('code' => $code)),
                    'checkEnvironmentUrl' => $this->generateUrl('admin_app_package_update_check_environment', array('id' => $apps[$code]['package']['id'])),
                ),
            );
        } catch (\Exception $e) {
            $result = array('isUpgrade' => false);
        }

        return $this->createJsonResponse($result);
    }

    protected function createResponseWithErrors($errors)
    {
        if (empty($errors)) {
            return $this->createJsonResponse(array('status' => 'ok'));
        }

        if (isset($errors['index'])) {
            return $this->createJsonResponse($errors);
        }

        return $this->createJsonResponse(array('status' => 'error', 'errors' => $errors));
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
