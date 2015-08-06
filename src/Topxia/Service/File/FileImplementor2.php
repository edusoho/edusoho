<?php
namespace Topxia\Service\File;

interface FileImplementor2
{

    public function getFile($file);

	public function prepareUpload($params);

	public function initUpload($file);

}