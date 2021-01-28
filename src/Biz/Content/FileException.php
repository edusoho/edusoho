<?php

namespace Biz\Content;

use AppBundle\Common\Exception\AbstractException;

class FileException extends AbstractException
{
    const EXCEPTION_MODULE = 10;

    const FILE_NOT_FOUND = 4041001;
    const FILE_GROUP_INVALID = 4031002;
    const FILE_NOT_UPLOAD = 5001003;
    const FILE_HANDLE_ERROR = 5001004;
    const FILE_UPLOAD_NOT_ALLOWED = 5001005;
    const FILE_PARSE_URI_FAILED = 5001006;
    const FILE_DIRECTORY_UN_WRITABLE = 5001007;
    const FILE_EXT_PARSE_FAILED = 5001008;
    const FILE_TYPE_ERROR = 5001009;
    const FILE_SIZE_LIMIT = 5001010;
    const FILE_EMPTY_ERROR = 5001011;
    const FILE_AUTH_URL_INVALID = 5001012;

    public $messages = [
        4041001 => 'exception.file.not_found',
        4031002 => 'exception.file.group_invalid',
        5001003 => 'exception.file.not_upload',
        5001004 => 'exception.file.handle_error',
        5001005 => 'exception.file.upload_not_allowed',
        5001006 => 'exception.file.parse_uri_failed',
        5001007 => 'exception.file.directory_un_writable',
        5001008 => 'exception.file.ext_parse_failed',
        5001009 => 'exception.file.type_error',
        5001010 => 'exception.file.size_limit',
        5001011 => 'exception.file.empty_error',
        5001012 => 'exception.file.auth_url_invalid',
    ];
}
