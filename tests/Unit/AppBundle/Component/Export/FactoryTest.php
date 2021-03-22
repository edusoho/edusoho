<?php

namespace Tests\Unit\AppBundle\Component\Export;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\Factory;
use Biz\BaseTestCase;

class FactoryTest extends BaseTestCase
{
    private $map = [
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
    ];

    public function testCreate()
    {
        $container = self::$appKernel->getContainer();
        $factory = new Factory($container);

        $this->assertNotEmpty($factory->create('invite-records'));
    }

    public function testFactoryMap()
    {
        $container = self::$appKernel->getContainer();
        $factory = new Factory($container);

        $map = $this->map;

        foreach ($map as $value) {
            $result = ReflectionUtils::invokeMethod($factory, 'exportMap', [$value]);
            $this->assertNotEmpty($result);
        }

        $faqPluginExport = 'faq:question-like';
        $container->set('faq_export_map', new ExportMap());
        $result = ReflectionUtils::invokeMethod($factory, 'exportMap', [$faqPluginExport]);
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
        ReflectionUtils::invokeMethod($factory, 'exportMap', [$faqPluginExport]);
    }
}

class ExportMap
{
    public function getMap()
    {
        return [
            'faq:question-like' => 'FaqPlugin\Biz\Exporter\QuestionLikes',
        ];
    }
}
