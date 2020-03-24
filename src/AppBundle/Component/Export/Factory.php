<?php

namespace AppBundle\Component\Export;

use AppBundle\Common\Exception\UnexpectedValueException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Factory
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $name
     * @param $conditions
     *
     * @return Exporter
     */
    public function create($name, $conditions = array())
    {
        $export = $this->exportMap($name);

        return new $export($this->container, $conditions);
    }

    private function exportMap($name)
    {
        $map = array(
            'invite-records' => 'AppBundle\Component\Export\Invite\InviteRecordsExporter',
            'user-invite-records' => 'AppBundle\Component\Export\Invite\InviteUserRecordsExporter',
            'order' => 'AppBundle\Component\Export\Order\OrderExporter',
            'course-overview-student-list' => 'AppBundle\Component\Export\Course\OverviewStudentExporter',
            'course-overview-task-list' => 'AppBundle\Component\Export\Course\OverviewTaskExporter',
            'course-overview-normal-task-detail' => 'AppBundle\Component\Export\Course\OverviewNormalTaskDetailExporter',
            'course-overview-testpaper-task-detail' => 'AppBundle\Component\Export\Course\OverviewTestpaperTaskDetailExporter',
            'bill-cash-flow' => 'AppBundle\Component\Export\Bill\CashBillExporter',
            'bill-coin-flow' => 'AppBundle\Component\Export\Bill\CoinBillExporter',
            'user-learn-statistics' => 'AppBundle\Component\Export\UserLearnStatistics\UserLearnStatisticsExporter',
            'course-students' => 'AppBundle\Component\Export\Course\StudentExporter',
            'invoice-records' => 'InvoicePlugin\Component\Export\Invoice\InvoiceRecordsExporter',
            'course-live-statistics-list' => 'AppBundle\Component\Export\Course\CourseLiveStatisticsExporter',
            'course-live-statistics-checkin-list' => 'AppBundle\Component\Export\Course\LiveStatisticsCheckinListExporter',
            'course-live-statistics-visitor-list' => 'AppBundle\Component\Export\Course\LiveStatisticsVisitorListExporter',
        );

        $names = explode(':', $name);
        if (2 == count($names)) {
            $map = array_merge($map, $this->container->get($names[0].'_export_map')->getMap());
        }
        if (empty($map[$name])) {
            throw new UnexpectedValueException('exporter class could not be found');
        }

        return $map[$name];
    }
}
