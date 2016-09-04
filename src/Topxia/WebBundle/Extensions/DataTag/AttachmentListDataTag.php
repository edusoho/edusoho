<?php
namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;

class AttachmentListDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取附件列表
     *
     * @param  array $arguments     参数
     * @return array 附件列表
     */
    public function getData(array $arguments)
    {
        if (!ArrayToolKit::requireds($arguments, array('targetType', 'targetId'))) {
            throw new \Exception("缺少参数，无法获取附件列表");
        }
        $type       = 'attachment';
        $targetType = $arguments['targetType'];
        $targetId   = $arguments['targetId'];
        return $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType($targetType, $targetId, $type);
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}
