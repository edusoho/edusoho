<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\RuntimeException;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\FaceInspection\Service\FaceInspectionService;
use Biz\System\Service\SettingService;
use ESCloud\SDK\ESCloudSDK;
use Symfony\Component\HttpFoundation\Request;

class FaceInspectionController extends BaseController
{
    public function overviewAction(Request $request)
    {
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getFaceInspectionService()->countUsersJoinUserFace($conditions),
            20
        );
        $users = $this->getFaceInspectionService()->searchUsersJoinUserFace(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userIds = empty($users) ? [-1] : ArrayToolkit::column($users, 'id');
        $userFaces = $this->getFaceInspectionService()->searchUserFaces(['user_ids' => $userIds], [], 0, PHP_INT_MAX);
        $userFaces = ArrayToolkit::index($userFaces, 'user_id');

        return $this->render('admin-v2/cloud-center/face-inspection/overview.html.twig', [
            'users' => $users,
            'userFaces' => $userFaces,
            'paginator' => $paginator,
        ]);
    }

    public function userFaceDetailAction(Request $request, $userId)
    {
        $userFace = $this->getFaceInspectionService()->getUserFaceByUserId($userId);

        return $this->render('admin-v2/cloud-center/face-inspection/detail-modal.html.twig', [
            'userFace' => $userFace,
        ]);
    }

    public function settingAction(Request $request)
    {
        if (!$this->isVisibleCloud()) {
            return $this->redirect($this->generateUrl('admin_v2_my_cloud_overview'));
        }

        $settings = $this->getSettingService()->get('storage', []);
        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            $this->setFlashMessage('warning', 'admin.cloud.license.has_no_license');

            return $this->redirect($this->generateUrl('admin_v2_setting_cloud_key_update'));
        }

        try {
            $sdk = new ESCloudSDK(['access_key' => $settings['cloud_access_key'], 'secret_key' => $settings['cloud_secret_key']]);
            $service = $sdk->getInspectionService();
            $account = $service->getAccount();
            $setting = $this->getSettingService()->get('cloud_facein', []);
            $setting['enabled'] = empty($setting['enabled']) ? 0 : 1;
            if (!empty($account)) {
                $setting['enabled'] = empty($account['enabled']) ? 0 : $setting['enabled'];
                $setting['account'] = $account;
                $this->getSettingService()->set('cloud_facein', $setting);
            }

            return $this->render('admin-v2/cloud-center/face-inspection/setting.html.twig', [
                'account' => $account,
            ]);
        } catch (RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/face-inspection/error.html.twig', []);
        }
    }

    public function updateAction(Request $request)
    {
        $enabled = $request->request->get('enabled', 0);
        $setting = $this->getSettingService()->get('cloud_facein', []);
        $setting['enabled'] = $enabled;
        $this->getSettingService()->set('cloud_facein', $setting);

        return $this->createJsonResponse(true);
    }

    public function generateLinkAction(Request $request)
    {
        $setting = $this->getSettingService()->get('cloud_facein', []);
        if (empty($setting['capture_link_code'])) {
            $setting['capture_link_code'] = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
            $this->getSettingService()->set('cloud_facein', $setting);
        }

        if ('POST' == $request->getMethod()) {
            $setting['capture_link_code'] = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
            $this->getSettingService()->set('cloud_facein', $setting);
            $url = $this->generateUrl('facein_capture_index', ['code' => $setting['capture_link_code']], true);
            if (false !== strpos($url, 'http://')) {
                $url = str_replace('http://', 'https://', $url);
            }

            return $this->createJsonResponse($url);
        }

        $url = $this->generateUrl('facein_capture_index', ['code' => $setting['capture_link_code']], true);

        if (false !== strpos($url, 'http://')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $this->render('admin-v2/cloud-center/face-inspection/capture-link-modal.html.twig', [
            'url' => $url,
        ]);
    }

    private function isVisibleCloud()
    {
        return $this->getEduCloudService()->isVisibleCloud();
    }

    /**
     * @return EduCloudService
     */
    protected function getEduCloudService()
    {
        return $this->createService('CloudPlatform:EduCloudService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return FaceInspectionService
     */
    protected function getFaceInspectionService()
    {
        return $this->createService('FaceInspection:FaceInspectionService');
    }
}
