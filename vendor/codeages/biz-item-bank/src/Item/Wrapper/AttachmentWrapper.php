<?php

namespace Codeages\Biz\ItemBank\Item\Wrapper;

use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;

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
        $item['attachments'] = ArrayToolkit::sort($item['attachments'], 'seq', SORT_ASC);
        if (empty($item['questions'])) {
            return $item;
        }

        $attachments = $this->getAttachmentService()->findAttachmentsByTargetIdsAndTargetType(
            ArrayToolkit::column($item['questions'], 'id'),
            'question'
        );
        $attachments = ArrayToolkit::sort($attachments, 'seq', SORT_ASC);
        $globalIds = ArrayToolkit::column($attachments, 'global_id');
        $attachments = ArrayToolkit::group($attachments, 'target_id');
        $files = $globalIds ? $this->getUploadFileService()->searchCloudFilesFromLocal([
            'globalIds' => $globalIds,
            'questionBank' => 1,
            'resType' => 'attachment',
        ], [], 0, PHP_INT_MAX) : [];
        $files = ArrayToolkit::index($files, 'globalId');
        foreach ($item['questions'] as &$question) {
            $question['attachments'] = empty($attachments[$question['id']]) ? [] : $attachments[$question['id']];
            foreach ($question['attachments'] as &$attachment) {
                $attachment['length'] = $files[$attachment['global_id']]['length'] ?? 0;
                if ('video' == $attachment['file_type']) {
                    $attachment['thumbnail'] = $files[$attachment['global_id']]['thumbnail'] ?? null;
                }
            }
        }

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
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }
}
