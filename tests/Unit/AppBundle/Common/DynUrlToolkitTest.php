<?php

namespace AppBundle\Common\Tests;

use AppBundle\Common\DynUrlToolkit;
use Biz\BaseTestCase;

class DynUrlToolkitTest extends BaseTestCase
{
    public function testGetUrlWithSupportedType()
    {
        $this->biz['template_extension.live'] = array(
            'course-manage/create-modal' => 'course-manage/create-modal/one-to-one.html.twig',
        );

        $result = DynUrlToolkit::getUrl($this->biz, 'course-manage/create-modal.html.twig', array('type' => 'live'));
        $this->assertEquals('course-manage/create-modal/one-to-one.html.twig', $result);
    }

    public function testGetUrlWithUnsupportedType()
    {
        $this->biz['template_extension.reservation'] = array(
            'course-manage/create-modal' => 'plugin/course-manage/create-modal/one-to-one.html.twig',
        );

        $result = DynUrlToolkit::getUrl($this->biz, 'course-manage/create-modal.html.twig', array('type' => 'normal'));
        $this->assertEquals('course-manage/create-modal.html.twig', $result);
    }
}
