<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\System\Service\SettingService;
use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class EduCloudController extends BaseController
{
    //云视频概览页
    public function videoOverviewAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/edu-cloud/video/trial.html.twig', array());
        }

        if (!($this->isVisibleCloud())) {
            return $this->redirect($this->generateUrl('admin_my_cloud_overview'));
        }

        $storageSetting = $this->getSettingService()->get('storage', array());
        //云端视频判断
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get('/me/storage/overview');
        } catch (\RuntimeException $e) {
            return $this->render('admin/edu-cloud/video-error.html.twig', array());
        }
        if ((isset($storageSetting['upload_mode']) && 'local' == $storageSetting['upload_mode']) || !isset($storageSetting['upload_mode'])) {
            return $this->render('admin/edu-cloud/video/without-enable.html.twig');
        }

        $overview['video']['isBuy'] = isset($overview['video']['isBuy']) ? false : true;
        $overview['yearPackage']['isBuy'] = isset($overview['yearPackage']['isBuy']) ? false : true;

        $spaceItems = $this->dealItems($overview['video']['spaceItems']);
        $flowItems = $this->dealItems($overview['video']['flowItems']);

        return $this->render('admin/edu-cloud/video/overview.html.twig', array(
            'video' => $overview['video'],
            'space' => isset($overview['space']) ? $overview['space'] : null,
            'flow' => isset($overview['flow']) ? $overview['flow'] : null,
            'yearPackage' => $overview['yearPackage'],
            'spaceItems' => $spaceItems,
            'flowItems' => $flowItems,
        ));
    }

    public function attachmentSettingAction(Request $request)
    {
        $attachment = $this->getSettingService()->get('cloud_attachment', array());
        $defaultData = array('article' => 0, 'course' => 0, 'classroom' => 0, 'group' => 0, 'question' => 0);
        $default = array_merge($defaultData, array('enable' => 0, 'fileSize' => 500));
        $attachment = array_merge($default, $attachment);

        if ('POST' == $request->getMethod()) {
            $attachment = $request->request->all();
            $attachment = array_merge($default, $attachment);
            $this->getSettingService()->set('cloud_attachment', $attachment);
            $this->setFlashMessage('success', 'site.save.success');
        }
        //云端视频判断
        try {
            $api = CloudAPIFactory::create('root');
            $info = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/cloud-center/edu-cloud/video-error.html.twig', array());
        }

        return $this->render('admin-v2/cloud-center/edu-cloud/cloud-attachment.html.twig', array(
            'attachment' => $attachment,
            'info' => $info,
        ));
    }

    private function dealItems($data)
    {
        if (empty($data)) {
            for ($i = 7; $i > 0; --$i) {
                $items['date'][] = date('Y-m-d', strtotime('-'.$i.'days'));
                $items['amount'][] = 0;
            }

            return $items;
        }

        foreach ($data as $value) {
            $items['date'][] = $value['date'];
            $items['amount'][] = $value['amount'];
        }

        return $items;
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
}
