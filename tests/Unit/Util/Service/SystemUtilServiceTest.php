<?php

namespace Tests\Unit\Util\Service;

use Biz\BaseTestCase;

class SystemUtilServiceTest extends BaseTestCase
{
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getSystemUtilService()
    {
        return $this->createService('Util:SystemUtilService');
    }
}
