<?php
namespace Topxia\Service\File;

interface UploadFileService2
{
    public function getFile($id);

	public function initUpload($params);

	public function finishedUpload($params);

    public function setFileProcessed($params);

}