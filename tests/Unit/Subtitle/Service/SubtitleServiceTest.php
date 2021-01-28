<?php

namespace Tests\Unit\Subtitle\Service;

use Biz\BaseTestCase;

class SubtitleServiceTest extends BaseTestCase
{
    public function testFindSubtitlesByMediaId()
    {
        $this->mockUploadFileService();
        $subtitle = $this->createSubtitle();
        $subtitle2 = $this->createSubtitle2();

        $subtitles = $this->getSubtitleService()->findSubtitlesByMediaId($subtitle['mediaId']);
        $this->assertEquals(2, count($subtitles));
    }

    public function testGetSubtitle()
    {
        $this->mockUploadFileService();
        $subtitle = $this->createSubtitle();
        $subtitleGetted = $this->getSubtitleService()->getSubtitle($subtitle['id']);
        $this->assertEquals($subtitle['id'], $subtitleGetted['id']);
    }

    public function testDeleteSubtitle()
    {
        $this->mockUploadFileService();
        $subtitle = $this->createSubtitle();
        $result = $this->getSubtitleService()->deleteSubtitle($subtitle['id']);
        $this->assertEquals(true, $result);
    }

    public function testSetSubtitlesUrls()
    {
        $this->mockBiz(
            'Subtitle:SubtitleDao',
            array(
                array(
                    'functionName' => 'findSubtitlesByMediaId',
                    'withParams' => array(222),
                    'returnValue' => array(
                        array(
                            'subtitleId' => 2221,
                        ),
                        array(
                            'subtitleId' => 2222,
                        ),
                    ),
                ),
            )
        );

        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'findFilesByIds',
                    'withParams' => array(array(2221, 2222), true, array('resType' => 'sub')),
                    'returnValue' => array(
                        array(
                            'type' => 'subtitle',
                            'id' => 2221,
                            'convertStatus' => 'success',
                        ),
                        array(
                            'type' => 'subtitle',
                            'id' => 2222,
                            'convertStatus' => 'failed',
                        ),
                    ),
                ),
                array(
                    'functionName' => 'getDownloadMetas',
                    'withParams' => array(2221, false),
                    'returnValue' => array(
                        'type' => 'subtitle',
                        'url' => 'url1',
                    ),
                ),
                array(
                    'functionName' => 'getDownloadMetas',
                    'withParams' => array(2222, false),
                    'returnValue' => array(
                        'type' => 'subtitle',
                        'url' => 'url2',
                    ),
                ),
            )
        );

        $result = $this->getSubtitleService()->setSubtitlesUrls(
            array('type' => 'video', 'mediaId' => 222),
            false
        );

        $this->assertArrayEquals(array('url1'), $result['subtitlesUrls']);
    }

    protected function mockUploadFileService()
    {
        $fakeFile = array(
            'id' => 1,
            'type' => 'subtitle',
        );
        $fakeFiles = array(
            array(
                'id' => 1,
                'type' => 'subtitle',
                'convertStatus' => 'success',
            ),
        );
        $fakeDownloadFile = array(
            'url' => 'www.edusoho.com',
        );
        $this->mockBiz('File:UploadFileService', array(
            array('functionName' => 'getFile', 'runTimes' => 1, 'returnValue' => $fakeFile),
            array('functionName' => 'deleteFile', 'runTimes' => 1, 'returnValue' => true),
            array('functionName' => 'getDownloadMetas', 'runTimes' => 1, 'returnValue' => $fakeDownloadFile),
            array('functionName' => 'findFilesByIds', 'runTimes' => 1, 'returnValue' => $fakeFiles),
        ));
    }

    protected function createSubtitle()
    {
        $fileds = array(
            'name' => 'subtitle1',
            'subtitleId' => '1',
            'mediaId' => '2',
            'ext' => 'srt',
            'createdTime' => time(),
        );

        return $this->getSubtitleService()->addSubtitle($fileds);
    }

    protected function createSubtitle2()
    {
        $fileds = array(
            'name' => 'subtitle2',
            'subtitleId' => '3',
            'mediaId' => '2',
            'ext' => 'srt',
            'createdTime' => time(),
        );

        return $this->getSubtitleService()->addSubtitle($fileds);
    }

    protected function getSubtitleService()
    {
        return $this->createService('Subtitle:SubtitleService');
    }
}
