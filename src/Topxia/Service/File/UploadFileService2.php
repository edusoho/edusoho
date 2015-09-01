<?php
namespace Topxia\Service\File;

interface UploadFileService2
{
    public function getFile($id);

    /**
     * 获得文件基础信息
     */
    public function getThinFile($id);

	public function initUpload($params);

	public function finishedUpload($params);

    public function setFileProcessed($params);

    public function deleteFiles(array $ids);

    public function increaseFileUsedCount($id);

    public function decreaseFileUsedCount($id);

}