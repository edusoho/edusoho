<?php
namespace Topxia\Service\Subtitle\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Subtitle\SubtitleService;
use Topxia\Common\Exception\ResourceNotFoundException;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Common\Exception\UnexpectedValueException;
use Topxia\Common\ArrayToolkit;

class SubtitleServiceImpl extends BaseService implements SubtitleService
{
    public function findSubtitlesByMediaId($mediaId, $ssl = false)
    {
        $subtitles = $this->getSubtitleDao()->findSubtitlesByMediaId($mediaId);

        if (empty($subtitles)) {
            return array();
        }

        $subtitles = $this->fillMetas($subtitles, $ssl);

        return array_values($subtitles);
    }

    public function getSubtitle($id)
    {
        $subtitle = $this->getSubtitleDao()->getSubtitle($id);
        $fileId = $subtitle['subtitleId'];
        $file = $this->getUploadFileService()->getFile($fileId);
        if (empty($file) || $file["type"] != "subtitle") {
            throw new ResourceNotFoundException('subtitleUploadFile', $fileId);
        }

        $downloadFile = $this->getUploadFileService()->getDownloadMetas($fileId);

        $subtitle['url'] = $downloadFile['url'];

        return $subtitle;
    }

    public function addSubtitle($subtitle)
    {
        if (empty($subtitle)) {
            throw new InvalidArgumentException('添加失败');
        }

        //提供的服务只允许最多添加4个字幕
        $existSubtitles = $this->getSubtitleDao()->findSubtitlesByMediaId($subtitle['mediaId']);

        if (count($existSubtitles) >= 4) {
            throw new UnexpectedValueException('最多允许添加4个字幕');
        }

        $subtitle = $this->filterSubtitleFields($subtitle);
        $record = $this->getSubtitleDao()->addSubtitle($subtitle);
        $subtitles = $this->fillMetas(array($record));
        return array_pop($subtitles);
    }

    public function deleteSubtitle($id)
    {
        $subtitle = $this->getSubtitle($id);
        if (empty($subtitle)) {
            throw new ResourceNotFoundException('subtitle', $id);
        }

        $this->getSubtitleDao()->deleteSubtitle($id);
        $this->getUploadFileService()->deleteFile($subtitle['subtitleId']);

        return true;
    }

    protected function filterSubtitleFields($fields)
    {

        if (!ArrayToolkit::requireds($fields, array('name', 'subtitleId', 'mediaId'))) {
            throw new InvalidArgumentException("参数不正确");
        }

        $subtitle = array();

        $subtitle['name'] = rtrim($fields['name'], '.srt');
        if (empty($fields['ext'])) {
            $subtitle['ext'] = (string) substr(strrchr($fields['name'], '.'), 1);
        } else {
            $subtitle['ext'] = $fields['ext'];
        }
        $subtitle['subtitleId']  = $fields['subtitleId'];
        $subtitle['mediaId']     = $fields['mediaId'];
        $subtitle['createdTime'] = time();

        return $subtitle;
    }

    protected function fillMetas($subtitles, $ssl = false)
    {
        $subtitles = ArrayToolkit::index($subtitles, 'subtitleId');

        $fileIds = ArrayToolkit::column($subtitles, 'subtitleId');
        $files = $this->getUploadFileService()->findFilesByIds($fileIds, true, array('resType' => 'sub'));
        foreach ($files as $file) {
            if (!($file["type"] == "subtitle" || $file["targetType"] == "subtitle")) {
                continue;
            }
            $downloadFile = $this->getUploadFileService()->getDownloadMetas($file['id'], $ssl);
            $subtitles[$file['id']]['url'] = $downloadFile['url'];
            $subtitles[$file['id']]['convertStatus'] = $file['convertStatus'];
        }

        return $subtitles;
    }

    protected function getSubtitleDao()
    {
        return $this->createDao('Subtitle.SubtitleDao');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }
}
