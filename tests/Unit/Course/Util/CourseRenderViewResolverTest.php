<?php

namespace Tests\Unit\Course\Util;

use Biz\BaseTestCase;
use Biz\Course\Util\CourseRenderViewResolver;

class CourseRenderViewResolverTest extends BaseTestCase
{
    public function testGenerateRenderViewWithCourseType()
    {
        $resolver = new CourseRenderViewResolver($this->biz);

        $this->biz['template_extension.live'] = array(
            'course-manage/create-modal' => 'course-manage/create-modal/one-to-one.html.twig',
        );

        $result = $resolver->generateRenderView(
            'course-manage/create-modal.html.twig',
            array('course' => array('type' => 'live'))
        );

        $this->assertEquals('course-manage/create-modal/one-to-one.html.twig', $result);
    }

    public function testGenerateRenderViewWithCourseSetType()
    {
        $resolver = new CourseRenderViewResolver($this->biz);

        $this->biz['template_extension.reservation'] = array(
            'course-manage/create-modal' => 'plugins/course-manage/create-modal/one-to-one.html.twig',
        );

        $result = $resolver->generateRenderView(
            'course-manage/create-modal.html.twig',
            array('courseSet' => array('type' => 'reservation'))
        );

        $this->assertEquals('plugins/course-manage/create-modal/one-to-one.html.twig', $result);
    }

    public function testGenerateRenderViewWithEmptyType()
    {
        $resolver = new CourseRenderViewResolver($this->biz);

        $this->biz['template_extension.reservation'] = array(
            'course-manage/create-modal' => 'plugins/course-manage/create-modal/one-to-one.html.twig',
        );

        $result = $resolver->generateRenderView(
            'course-manage/create-modal.html.twig',
            array('abc' => array('type' => 'one-to-one'))
        );

        $this->assertEquals('course-manage/create-modal.html.twig', $result);
    }
}
