<?php

namespace AppBundle\Component\Export;

use AppBundle\Common\Exception\UnexpectedValueException;
use AppBundle\Component\Export\Bill\CashBillExporter;
use AppBundle\Component\Export\Bill\CoinBillExporter;
use AppBundle\Component\Export\Classroom\ClassroomCourseStatisticsExporter;
use AppBundle\Component\Export\Classroom\ClassroomMemberStatisticsExporter;
use AppBundle\Component\Export\Classroom\ClassroomSignStatisticsExporter;
use AppBundle\Component\Export\Classroom\ClassroomStatisticsCourseLearnDetailExporter;
use AppBundle\Component\Export\Classroom\ClassroomStatisticsCoursesLearnExporter;
use AppBundle\Component\Export\Classroom\ClassroomStatisticsExporter;
use AppBundle\Component\Export\Classroom\ClassroomStatisticsStudentsLearnExporter;
use AppBundle\Component\Export\Course\CourseLiveStatisticsExporter;
use AppBundle\Component\Export\Course\LiveStatisticsCheckinListExporter;
use AppBundle\Component\Export\Course\LiveStatisticsVisitorListExporter;
use AppBundle\Component\Export\Course\OverviewNormalTaskDetailExporter;
use AppBundle\Component\Export\Course\OverviewStudentExporter;
use AppBundle\Component\Export\Course\OverviewTaskExporter;
use AppBundle\Component\Export\Course\OverviewTestpaperTaskDetailExporter;
use AppBundle\Component\Export\InformationCollect\InformationCollectDetailExporter;
use AppBundle\Component\Export\Invite\InviteRecordsExporter;
use AppBundle\Component\Export\Invite\InviteUserRecordsExporter;
use AppBundle\Component\Export\ItemBankExercise\StudentExporter;
use AppBundle\Component\Export\Order\OrderExporter;
use AppBundle\Component\Export\UserLearnStatistics\UserCourseStatisticsExporter;
use AppBundle\Component\Export\UserLearnStatistics\UserLearnStatisticsExporter;
use AppBundle\Component\Export\UserLearnStatistics\UserLessonStatisticsExporter;
use InvoicePlugin\Component\Export\Invoice\InvoiceRecordsExporter;
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
    public function create($name, $conditions = [])
    {
        $export = $this->exportMap($name);

        return new $export($this->container, $conditions);
    }

    private function exportMap($name)
    {
        $map = [
            'invite-records' => InviteRecordsExporter::class,
            'user-invite-records' => InviteUserRecordsExporter::class,
            'order' => OrderExporter::class,
            'course-overview-student-list' => OverviewStudentExporter::class,
            'course-overview-task-list' => OverviewTaskExporter::class,
            'course-overview-normal-task-detail' => OverviewNormalTaskDetailExporter::class,
            'course-overview-testpaper-task-detail' => OverviewTestpaperTaskDetailExporter::class,
            'bill-cash-flow' => CashBillExporter::class,
            'bill-coin-flow' => CoinBillExporter::class,
            'user-learn-statistics' => UserLearnStatisticsExporter::class,
            'user-lesson-statistics' => UserLessonStatisticsExporter::class,
            'user-course-statistics' => UserCourseStatisticsExporter::class,
            'course-students' => \AppBundle\Component\Export\Course\StudentExporter::class,
            'item-bank-exercise-students' => StudentExporter::class,
            'invoice-records' => InvoiceRecordsExporter::class,
            'course-live-statistics-list' => CourseLiveStatisticsExporter::class,
            'course-live-statistics-checkin-list' => LiveStatisticsCheckinListExporter::class,
            'course-live-statistics-visitor-list' => LiveStatisticsVisitorListExporter::class,
            'classroom-statistics' => ClassroomStatisticsExporter::class,
            'classroom-member-statistics' => ClassroomMemberStatisticsExporter::class,
            'classroom-course-statistics' => ClassroomCourseStatisticsExporter::class,
            'classroom-sign-statistics' => ClassroomSignStatisticsExporter::class,
            'classroom-statistics-students-learn' => ClassroomStatisticsStudentsLearnExporter::class,
            'classroom-statistics-course-learn' => ClassroomStatisticsCoursesLearnExporter::class,
            'classroom-statistics-course-learn-detail' => ClassroomStatisticsCourseLearnDetailExporter::class,
            'information-collect-detail' => InformationCollectDetailExporter::class,
        ];

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
