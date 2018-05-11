<?php

namespace AppBundle\Common\Tests;

use AppBundle\Common\DynUrlToolkit;
use Biz\BaseTestCase;

class DynUrlToolkitTest extends BaseTestCase
{
    public function testGetUrlWithSupportedType()
    {
        $this->biz['course-manage/create-modal'] = array(
            'one-to-one' => 'plugins/course-manage/create-modal/one-to-one.html.twig',
        );

        $result = DynUrlToolkit::getUrl($this->biz, 'course-manage/create-modal.html.twig', array('type' => 'one-to-one'));
        $this->assertEquals('plugins/course-manage/create-modal/one-to-one.html.twig', $result);
    }

    public function testGetUrlWithUnsupportedType()
    {
        $this->biz['course-manage/create-modal'] = array(
            'one-to-one' => 'plugins/course-manage/create-modal/one-to-one.html.twig',
        );

        $result = DynUrlToolkit::getUrl($this->biz, 'course-manage/create-modal.html.twig', array('type' => 'one'));
        $this->assertEquals('course-manage/create-modal.html.twig', $result);
    }
}
