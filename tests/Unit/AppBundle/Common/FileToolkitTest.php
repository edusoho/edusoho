<?php

namespace AppBundle\Common\Tests;

use AppBundle\Common\FileToolkit;
use Biz\BaseTestCase;

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

    public function testGetFileTypeByVideoExtension()
    {
        $extension = FileToolkit::getFileTypeByExtension('mp4');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('avi');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('mpg');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('flv');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('f4v');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('wmv');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('mov');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('rmvb');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('mkv');
        $this->assertEquals('video', $extension);

        $extension = FileToolkit::getFileTypeByExtension('m4v');
        $this->assertEquals('video', $extension);
    }

    public function testGetFileTypeByAudioExtension()
    {
        $extension = FileToolkit::getFileTypeByExtension('mp3');
        $this->assertEquals('audio', $extension);

        $extension = FileToolkit::getFileTypeByExtension('wma');
        $this->assertEquals('audio', $extension);
    }

    public function testGetFileTypeByImageExtension()
    {
        $extension = FileToolkit::getFileTypeByExtension('jpg');
        $this->assertEquals('image', $extension);

        $extension = FileToolkit::getFileTypeByExtension('jpeg');
        $this->assertEquals('image', $extension);

        $extension = FileToolkit::getFileTypeByExtension('png');
        $this->assertEquals('image', $extension);

        $extension = FileToolkit::getFileTypeByExtension('gif');
        $this->assertEquals('image', $extension);

        $extension = FileToolkit::getFileTypeByExtension('bmp');
        $this->assertEquals('image', $extension);
    }

    public function testGetFileTypeByDocumentExtension()
    {
        $extension = FileToolkit::getFileTypeByExtension('doc');
        $this->assertEquals('document', $extension);

        $extension = FileToolkit::getFileTypeByExtension('docx');
        $this->assertEquals('document', $extension);

        $extension = FileToolkit::getFileTypeByExtension('pdf');
        $this->assertEquals('document', $extension);

        $extension = FileToolkit::getFileTypeByExtension('xls');
        $this->assertEquals('document', $extension);

        $extension = FileToolkit::getFileTypeByExtension('xlsx');
        $this->assertEquals('document', $extension);

        $extension = FileToolkit::getFileTypeByExtension('txt');
        $this->assertEquals('document', $extension);
    }

    public function testGetFileTypeByPptExtension()
    {
        $extension = FileToolkit::getFileTypeByExtension('ppt');
        $this->assertEquals('ppt', $extension);

        $extension = FileToolkit::getFileTypeByExtension('pptx');
        $this->assertEquals('ppt', $extension);
    }

    public function testGetFileTypeByFlashExtension()
    {
        $extension = FileToolkit::getFileTypeByExtension('swf');
        $this->assertEquals('flash', $extension);
    }

    public function testGetFileTypeByOtherExtension()
    {
        $extension = FileToolkit::getFileTypeByExtension('md');
        $this->assertEquals('other', $extension);

        $extension = FileToolkit::getFileTypeByExtension('exe');
        $this->assertEquals('other', $extension);
    }
}
