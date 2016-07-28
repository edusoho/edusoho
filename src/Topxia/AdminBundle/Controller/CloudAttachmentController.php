<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudAttachmentController extends BaseController
{
    public function indexAction(Request $request)
    {
        try {
            $api    = CloudAPIFactory::create('leaf');
            $result = $api->get("/me");
        } catch (\RuntimeException $e) {
            return $this->render('TopxiaAdminBundle:CloudAttachment:api-error.html.twig', array());
        }

        $storageSetting = $this->getSettingService()->get('storage', array());

        if (isset($result['hasStorage']) && $result['hasStorage'] == '1' && $storageSetting['upload_mode'] == "cloud") {
            return $this->render('TopxiaAdminBundle:CloudAttachment:index.html.twig');
        }

        return $this->render('TopxiaAdminBundle:CloudAttachment:error.html.twig', array());
    }

    public function renderAction(Request $request)
    {
        $conditions = $request->query->all();

        if (!isset($conditions['processStatus'])) {
            $conditions['processStatus'] = '';
        }

        $count     = $this->getCloudAttachment()->searchFileCount($conditions);
        $paginator = new Paginator($this->get('request'), $count, 20);

        $materials = $this->getCloudAttachment()->searchFiles(
            $conditions,
            $orderBy = array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        //  var_dump($materials);
        $materials = array_map(function ($material) {
            if (!array_key_exists('createdUserId', $material)) {
                $material['createdUserId'] = $material['userId'];
            }
            return $material;
        }, $materials);

        $createdUserIds = ArrayToolkit::column($materials, 'createdUserId');
        $createdUsers   = $this->getUserService()->findUsersByIds($createdUserIds);

        return $this->render('TopxiaAdminBundle:CloudAttachment:tbody.html.twig', array(
            'type'         => empty($conditions['type']) ? 'all' : $conditions['type'],
            'targetType'   => empty($conditions['targetType']) ? 'all' : $conditions['targetType'],
            'materials'    => $materials,
            'createdUsers' => $createdUsers,
            'paginator'    => $paginator
        ));
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getCloudAttachment()
    {
        return $this->createService('CloudAttachment.CloudAttachmentService');
    }
}
