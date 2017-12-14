<?php

namespace Tests\Unit\Component\Export;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Factory;
use AppBundle\Common\ReflectionUtils;

class FactoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $container = self::$appKernel->getContainer();
        $factory = new Factory($container);

        $map = array(
            'invite-records',
            'user-invite-records',
            'order',
            'course-overview-student-list',
            'course-overview-task-list',
            'course-overview-normal-task-detail',
            'course-overview-testpaper-task-detail',
            'bill-cash-flow',
            'bill-coin-flow',
            'user-learn-statistics',
        );

        foreach ($map as $value) {
            $result = ReflectionUtils::invokeMethod($factory, 'exportMap', array($value));
            $this->assertNotEmpty($result);
        }

        $faqPluginExport = 'faq:question-like';
        $container->set('faq_export_map', new ExportMap());
        $result = ReflectionUtils::invokeMethod($factory, 'exportMap', array($faqPluginExport));
        $this->assertEquals('FaqPlugin\Biz\Exporter\QuestionLikes', $result);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testException()
    {
        $container = self::$appKernel->getContainer();
        $factory = new Factory($container);
        $faqPluginExport = 'faq:question-test';
        $container->set('faq_export_map', new ExportMap());
        ReflectionUtils::invokeMethod($factory, 'exportMap', array($faqPluginExport));
    }
}

class ExportMap
{
    public function getMap()
    {
        return array(
            'faq:question-like' => 'FaqPlugin\Biz\Exporter\QuestionLikes',
        );
    }
}
