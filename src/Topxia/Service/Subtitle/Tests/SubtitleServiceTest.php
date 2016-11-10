<?php
namespace Topxia\Service\Subtitle\Tests;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;

class SubtitleServiceTest extends BaseTestCase
{
    public function testFindSubtitlesByMediaId()
    {
        $subtitle = $this->createSubtitle();
        $subtitle2 = $this->createSubtitle2();

        $subtitles = $this->getSubtitleService()->findSubtitlesByMediaId($subtitle['mediaId']);
        $this->assertEquals(2, count($subtitles));
    }

    public function testGetSubtitle()
    {
        $subtitle = $this->createSubtitle();
        $subtitleGetted = $this->getSubtitleService()->getSubtitle($subtitle['id']);
        $this->assertEquals($subtitle['id'], $subtitleGetted['id']);
    }

    public function testDeleteSubtitle()
    {
        $subtitle = $this->createSubtitle();
        $this->getSubtitleService()->deleteSubtitle($subtitle['id']);
        $subtitleDeleted = $this->getSubtitleService()->getSubtitle($subtitle['id']);
        $this->assertEquals(null, $subtitleDeleted);
    }

    protected function createSubtitle()
    {
        $fileds = array(
            'name'        => 'subtitle1',
            'subtitleId'  => '1',
            'mediaId'     => '2',
            'ext'         => 'srt',
            'createdTime' => time()
        );
        return $this->getSubtitleService()->addSubtitle($fileds);
    }

    protected function createSubtitle2()
    {
        $fileds = array(
            'name'        => 'subtitle2',
            'subtitleId'  => '3',
            'mediaId'     => '2',
            'ext'         => 'srt',
            'createdTime' => time()
        );
        return $this->getSubtitleService()->addSubtitle($fileds);
    }

    protected function getSubtitleService()
    {
        return $this->getServiceKernel()->createService('Subtitle.SubtitleService');
    }
}
