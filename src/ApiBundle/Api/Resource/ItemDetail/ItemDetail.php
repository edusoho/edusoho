<?php

namespace ApiBundle\Api\Resource\ItemDetail;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\CloudFile\Service\CloudFileService;
use Biz\CloudPlatform\Service\ResourceFacadeService;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;

class ItemDetail extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $req = $request->query->all();

        $attachment = $this->getAttachmentService()->getAttachmentByGlobalId($req['globalId']);
        if (empty($attachment)) {
            return ['result' => false, 'msg' => '文件不存在', 'data' => []];
        }

        $file = $this->getCloudFileService()->getByGlobalId($req['globalId']);
        if (empty($file)) {
            return ['result' => false, 'msg' => '文件不存在', 'data' => []];
        }

        if (in_array($file['type'], ['video', 'ppt', 'document', 'audio'])) {
            return $this->globalPlayer($file);
        }

        return ['result' => false, 'msg' => '无法预览该类文件', 'data' => []];
    }

    protected function globalPlayer($file)
    {
        $user = $this->getCurrentUser();
        $player = $this->getResourceFacadeService()->getPlayerContext($file);

        return ['result' => true, 'msg' => '', 'data' => [
            'token' => $player['token'],
            'resNo' => $file['globalId'],
            'user' => ['id' => $user['id'], 'name' => $user['nickname']],
            'type' => $file['type'],
        ]];
    }

    /**
     * @return ResourceFacadeService
     */
    protected function getResourceFacadeService()
    {
        return $this->service('CloudPlatform:ResourceFacadeService');
    }

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->service('ItemBank:Item:AttachmentService');
    }

    /**
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->service('CloudFile:CloudFileService');
    }
}
