<?php

namespace AppBundle\Command;

use Biz\Visualization\Service\ActivityDataDailyStatisticsService;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatisticsUserLearnDataCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('statistics:user-learn-data')
            ->addArgument('startDate', InputArgument::REQUIRED, '起始时间 like 2024-07-10 (含)')
            ->addArgument('endDate', InputArgument::REQUIRED, '结束时间 like 2024-09-14 (不含)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $period = new DatePeriod(
            new DateTime($input->getArgument('startDate')),
            new DateInterval('P1D'),
            new DateTime($input->getArgument('endDate'))
        );
        foreach ($period as $value) {
            $date = $value->format('Y-m-d');
            $startTime = strtotime($date);
            //学员学习数据统计
            $this->getActivityDataDailyStatisticsService()->statisticsUserStayDailyData($startTime, $startTime + 86400);
            $this->getActivityDataDailyStatisticsService()->statisticsUserLearnDailyData($startTime);

            //学员课程学习数据统计
            $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanStayDailyData($startTime, $startTime + 86400);
            $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanLearnDailyData($startTime);

            //学员课时学习数据统计
            $this->getActivityDataDailyStatisticsService()->statisticsPageStayDailyData($startTime, $startTime + 86400);
            $this->getActivityDataDailyStatisticsService()->statisticsVideoDailyData($startTime, $startTime + 86400);
            $this->getActivityDataDailyStatisticsService()->statisticsLearnDailyData($startTime);
            $output->writeln("<info>{$date}统计完成</info>");
        }
    }

    /**
     * @return ActivityDataDailyStatisticsService
     */
    private function getActivityDataDailyStatisticsService()
    {
        return $this->getBiz()->service('Visualization:ActivityDataDailyStatisticsService');
    }
}
