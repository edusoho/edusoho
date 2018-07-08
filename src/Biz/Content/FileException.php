<?php

namespace Biz\Content;

use AppBundle\Common\Exception\AbstractException;

class FileException extends AbstractException
{
    const EXCEPTION_MODUAL = '10';

    const FILE_NOT_FOUND = 4041001;
    const FILE_GROUP_INVALID = 4031002;
    const FILE_NOT_UPLOAD = 5001003;
    const FILE_HANDLE_ERROR = 5001004;



    public $messages = array(
        4041001 => 'exception.file.not_found',
        4031002 => 'exception.file.group_invalid',
        5001003 => 'exception.file.not_upload',
        5001004 => 'exception.file.handle_error'
    );
}
