<?php

namespace Codeages\Biz\ItemBank\Item\Wrapper;

use Biz\CloudFile\Service\CloudFileService;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use Topxia\Service\Common\ServiceKernel;

class AttachmentWrapper
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function wrap($item)
    {
        if (empty($item)) {
            return [];
        }

        $item['attachments'] = $this->getAttachmentService()->findAttachmentsByTargetIdAndTargetType($item['id'], 'item');
        if (empty($item['questions'])) {
            return $item;
        }

        $attachments = $this->getAttachmentService()->findAttachmentsByTargetIdsAndTargetType(
            ArrayToolkit::column($item['questions'], 'id'),
            'question'
        );
        $sortAttachments = ArrayToolkit::group($attachments, 'module');
        foreach ($sortAttachments as $sortAttachment) {
            $attachments = ArrayToolkit::sort($attachments, 'seq', SORT_ASC);
        }
        $attachments = ArrayToolkit::group($attachments, 'target_id');

        $attachmentgroups =  ArrayToolkit::group($attachments, 'file_type');
        $golbalIds= ArrayToolkit::column($attachmentgroups,'golbal_id');

        $files = $this->getUploadFileService()->searchCloudFilesFromLocal([
            'globalIds'=>$golbalIds,
            'questionBank'=>1,
            'resType'=>'attachment'
        ],[],0,1);

        $files= ArrayToolkit::index($files,'golbalId');

        foreach ($item['questions'] as &$question) {
            $question['attachments'] = empty($attachments[$question['id']]) ? [] : $attachments[$question['id']];
            foreach ($question['attachments'] as &$attachment) {
                $attachment['length'] = $files[$attachment['golbal_id']['length']]??0;
                if($attachment['file_type'] == 'video') {
                    $attachment['cloudFile'] = $files[$attachment['golbal_id']]??null;
                }
            }
        }
file_put_contents('/tmp/log',json_encode($item), 8);
        return $item;
    }

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->biz->service('ItemBank:Item:AttachmentService');
    }

    /**
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->biz->service('CloudFile:CloudFileService');
    }
}
