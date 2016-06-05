<?php

namespace Topxia\Common\Tests;

use Topxia\Common\FileToolkit;
use Topxia\Service\Common\BaseTestCase;

class FileTookitTest extends BaseTestCase
{
    public function testGetMimeTypeByExtension()
    {
        $extension = FileToolkit::getMimeTypeByExtension('pdf');
        $this->assertEquals('application/pdf', $extension);

        $extension = FileToolkit::getMimeTypeByExtension('zip');
        $this->assertEquals('application/zip', $extension);
        
        $extension = FileToolkit::getMimeTypeByExtension('mpg');
        $this->assertEquals('video/mpeg', $extension);
        
    }

}
