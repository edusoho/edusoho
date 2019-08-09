<?php

namespace Tests\Unit\Content\Exception;

use Biz\BaseTestCase;
use Biz\Content\FileException;

class FileExceptionTest extends BaseTestCase
{
    public function testFileNotFound()
    {
        $exception = FileException::FILE_NOT_FOUND();

        $this->assertEquals('exception.file.not_found', $exception->getMessage());
    }

    public function testFileGroupInvalid()
    {
        $exception = FileException::FILE_GROUP_INVALID();

        $this->assertEquals('exception.file.group_invalid', $exception->getMessage());
    }

    public function testFileNotUpload()
    {
        $exception = FileException::FILE_NOT_UPLOAD();

        $this->assertEquals('exception.file.not_upload', $exception->getMessage());
    }

    public function testFileHandleError()
    {
        $exception = FileException::FILE_HANDLE_ERROR();

        $this->assertEquals('exception.file.handle_error', $exception->getMessage());
    }

    public function testFileUploadNotAllowed()
    {
        $exception = FileException::FILE_UPLOAD_NOT_ALLOWED();

        $this->assertEquals('exception.file.upload_not_allowed', $exception->getMessage());
    }

    public function testFileParseUriFailed()
    {
        $exception = FileException::FILE_PARSE_URI_FAILED();

        $this->assertEquals('exception.file.parse_uri_failed', $exception->getMessage());
    }

    public function testFileDirectoryUnWritable()
    {
        $exception = FileException::FILE_DIRECTORY_UN_WRITABLE();

        $this->assertEquals('exception.file.directory_un_writable', $exception->getMessage());
    }

    public function testFileExtParseFailed()
    {
        $exception = FileException::FILE_EXT_PARSE_FAILED();

        $this->assertEquals('exception.file.ext_parse_failed', $exception->getMessage());
    }

    public function testFileTypeError()
    {
        $exception = FileException::FILE_TYPE_ERROR();

        $this->assertEquals('exception.file.type_error', $exception->getMessage());
    }

    public function testFileSizeLimit()
    {
        $exception = FileException::FILE_SIZE_LIMIT();

        $this->assertEquals('exception.file.size_limit', $exception->getMessage());
    }

    public function testFileEmptyError()
    {
        $exception = FileException::FILE_EMPTY_ERROR();

        $this->assertEquals('exception.file.empty_error', $exception->getMessage());
    }

    public function testFileAuthUrlInvalid()
    {
        $exception = FileException::FILE_AUTH_URL_INVALID();

        $this->assertEquals('exception.file.auth_url_invalid', $exception->getMessage());
    }
}
