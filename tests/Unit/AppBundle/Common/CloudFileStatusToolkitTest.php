<?php

namespace Tests\Unit\AppBundle\Common;

use AppBundle\Common\CloudFileStatusToolkit;
use Biz\BaseTestCase;

class CloudFileStatusToolkitTest extends BaseTestCase
{
    public function testConvertProcessStatus()
    {
        $result = CloudFileStatusToolkit::convertProcessStatus('processing');

        $this->assertEquals('doing', $result);

        $result = CloudFileStatusToolkit::convertProcessStatus('test');
        $this->assertEquals('unknow', $result);
    }

    public function testGetTransCodeErrorMessageKeyByCode()
    {
        $result = CloudFileStatusToolkit::getTranscodeErrorMessageKeyByCode(41001);
        $this->assertEquals('cloud_file.transcoding_tips.error_code_41001', $result);

        $result = CloudFileStatusToolkit::getTranscodeErrorMessageKeyByCode('test');
        $this->assertEquals('cloud_file.transcoding_tips.default_error_message', $result);
    }

    public function testGetTransCodeFilterStatusCondition()
    {
        $result = CloudFileStatusToolkit::getTranscodeFilterStatusCondition('waiting');
        $this->assertEquals(array('processStatus' => 'waiting'), $result);

        $result = CloudFileStatusToolkit::getTranscodeFilterStatusCondition('test');
        $this->assertEmpty($result);
    }
}
