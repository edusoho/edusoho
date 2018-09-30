<?php

namespace Biz\File;

use AppBundle\Common\Exception\AbstractException

class UploadFileException extends AbstractException
{
    const EXCEPTION_MODUAL = 23;

    const NOTFOUND_FILE = 4042301;

    public $messages = array(
        4042301 => 'exception.uploadfile.file_not_found',
    );
}