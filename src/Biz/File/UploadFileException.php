<?php

namespace Biz\File;

use AppBundle\Common\Exception\AbstractException;

class UploadFileException extends AbstractException
{
    const EXCEPTION_MODULE = 23;

    const NOTFOUND_FILE = 4042301;

    const FORBIDDEN_MANAGE_FILE = 4032302;

    const ERROR_STATUS = 5002303;

    const PERMISSION_DENIED = 4032304;

    const IMPLEMENTOR_NOT_ALLOWED = 5002305;

    const UPLOAD_FAILED = 5002306;

    const GLOBALID_REQUIRED = 5002307;

    const LOCAL_CONVERT_NOT_SUPPORT = 5002308;

    const EXTENSION_NOT_ALLOWED = 5002309;

    const NOTFOUND_ATTACHMENT = 4042310;

    const ARGUMENTS_INVALID = 5002311;

    public $messages = [
        4042301 => 'exception.uploadfile.file_not_found',
        4032302 => 'exception.uploadfile.forbidden_manage_file',
        5002303 => 'exception.uploadfile.error_status',
        4032304 => 'exception.uploadfile.permission_denied',
        5002305 => 'exception.uploadfile.implementor_not_allowed',
        5002306 => 'exception.uploadfile.failed',
        5002307 => 'exception.uploadfile.globalId_required',
        5002308 => 'exception.uploadfile.local_convert_not_support',
        5002309 => 'exception.uploadfile.extension_not_allowed',
        4042310 => 'exception.uploadfile.not_found_attachment',
        5002311 => 'exception.uploadfile.arguments_invalid',
    ];
}
