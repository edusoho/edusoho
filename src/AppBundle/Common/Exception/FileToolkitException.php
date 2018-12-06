<?php

namespace AppBundle\Common\Exception;

class FileToolkitException extends AbstractException
{
    const EXCEPTION_MODUAL = 62;

    const NOT_IMAGE = 5006201;

    const NOT_FLASH = 5006202;

    const FILE_TYPE_ERROR = 5006203;

    public $messages = array(
        5006201 => 'exception.filetoolkit.not_image',
        5006202 => 'exception.filetoolkit.not_flash',
        5006203 => 'exception.filetoolkit.file_type_error',
    );
}
