<?php

namespace Biz\Subtitle\Service\Impl;

use Biz\BaseService;
use Biz\File\Service\UploadFileService;
use Biz\Subtitle\Dao\SubtitleDao;
use AppBundle\Common\ArrayToolkit;
use Biz\Subtitle\Service\SubtitleService;

class SubtitleServiceImpl extends BaseService implements SubtitleService
{
    public function searchSubtitles($conditions, $orderBy, $start, $limit)
    {
        return $this->getSubtitleDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function findSubtitlesByMediaIds($mediaIds)
    {
        return $this->searchSubtitles(array('mediaIds' => $mediaIds), array(), 0, \PHP_INT_MAX);
    }

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
        $subtitle = $this->getSubtitleDao()->get($id);
        $fileId = $subtitle['subtitleId'];
        $file = $this->getUploadFileService()->getFile($fileId);
        if (empty($file) || 'subtitle' != $file['type']) {
            throw $this->createNotFoundException("subtitleUploadFile{#$fileId} not found");
        }

        $downloadFile = $this->getUploadFileService()->getDownloadMetas($fileId);

        $subtitle['url'] = $downloadFile['url'];

        return $subtitle;
    }

    public function addSubtitle($subtitle)
    {
        if (empty($subtitle)) {
            throw $this->createInvalidArgumentException('create failed');
        }

        //提供的服务只允许最多添加4个字幕
        $existSubtitles = $this->findSubtitlesByMediaId($subtitle['mediaId']);

        if (count($existSubtitles) >= 4) {
            throw $this->createServiceException('at most four subtitles to be allowed');
        }

        $subtitle = $this->filterSubtitleFields($subtitle);
        $subtitle['createdTime'] = time();

        $record = $this->getSubtitleDao()->create($subtitle);
        $subtitles = $this->fillMetas(array($record));

        return array_pop($subtitles);
    }

    public function deleteSubtitle($id)
    {
        $subtitle = $this->getSubtitle($id);
        if (empty($subtitle)) {
            throw $this->createNotFoundException('subtitle{#id} not found');
        }

        $this->getSubtitleDao()->delete($id);
        $this->getUploadFileService()->deleteFile($subtitle['subtitleId']);

        return true;
    }

    public function setSubtitleUrls($lesson, $ssl = false)
    {
        $subtitles = $this->findSubtitlesByMediaId($lesson['mediaId'], $ssl);

        $subtitlesUrls = array();
        foreach ($subtitles as $subtitle) {
            if ('success' == $subtitle['convertStatus']) {
                $subtitlesUrls[] = $subtitle['url'];
            }
        }

        if (!empty($subtitlesUrls)) {
            $lesson['subtitleUrls'] = $subtitlesUrls;
        }

        return $lesson;
    }

    protected function fillMetas($subtitles, $ssl = false)
    {
        $subtitles = ArrayToolkit::index($subtitles, 'subtitleId');

        $fileIds = ArrayToolkit::column($subtitles, 'subtitleId');
        $files = $this->getUploadFileService()->findFilesByIds($fileIds, true, array('resType' => 'sub'));
        foreach ($files as $file) {
            if (!('subtitle' == $file['type'] || 'subtitle' == $file['targetType'])) {
                continue;
            }
            $downloadFile = $this->getUploadFileService()->getDownloadMetas($file['id'], $ssl);
            $subtitles[$file['id']]['url'] = $downloadFile['url'];
            $subtitles[$file['id']]['convertStatus'] = $file['convertStatus'];
        }

        return $subtitles;
    }

    protected function filterSubtitleFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name', 'subtitleId', 'mediaId'))) {
            throw $this->createInvalidArgumentException('parameter invalid');
        }

        $subtitle = array();

        $subtitle['name'] = rtrim($fields['name'], '.srt');
        if (empty($fields['ext'])) {
            $subtitle['ext'] = (string) substr(strrchr($fields['name'], '.'), 1);
        } else {
            $subtitle['ext'] = $fields['ext'];
        }
        $subtitle['subtitleId'] = $fields['subtitleId'];
        $subtitle['mediaId'] = $fields['mediaId'];

        return $subtitle;
    }

    /**
     * @return SubtitleDao
     */
    protected function getSubtitleDao()
    {
        return $this->createDao('Subtitle:SubtitleDao');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
