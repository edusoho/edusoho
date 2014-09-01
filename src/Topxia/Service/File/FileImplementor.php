<?php
namespace Topxia\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;


interface FileImplementor
{   
	public function getFile($file);

    public function addFile($targetType, $targetId, array $fileInfo=array(), UploadedFile $originalFile=null);

    public function convertFile($file, $status, $result=null, $callback = null);

    public function saveConvertResult($file, array $result = array());

    public function deleteFile($file, $deleteSubFile = true);

    public function makeUploadParams($params);

    public function reconvertFile($file, $convertCallback, $pipeline = null);

    public function getMediaInfo($key, $mediaType);
}