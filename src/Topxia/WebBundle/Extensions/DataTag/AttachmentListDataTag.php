<?php
namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;

class AttachmentListDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取资讯栏目
     *
     * 该DataTag返回了栏目的树结构，如只需显示第１级分类，只要循环时加判断depth = 1
     *
     * @param  array $arguments 参数
     * @return array 栏目
     */
    public function getData(array $arguments)
    {
        if (!ArrayToolKit::requireds($arguments, array('targetType', 'targetId'))) {
            throw new \Exception("缺少参数，无法获取附件列表");
        }
        $type        = 'attachment';
        $targetType  = $arguments['targetType'];
        $targetId    = $arguments['targetId'];
        $attachments = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType($targetType, $targetId, $type);
        return $attachments;
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}
