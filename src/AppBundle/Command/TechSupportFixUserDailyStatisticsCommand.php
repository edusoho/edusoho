<?php

namespace AppBundle\Command;

use Biz\UserLearnStatistics\Dao\DailyStatisticsDao;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TechSupportFixUserDailyStatisticsCommand extends BaseCommand
{
    public function configure()
    {
        /*
         * [
         *  {"userId":"1", "date":"2020-06-24", "learnedTime":"3600"},
         *  ...
         * ]
         */
        $this->setName('tech-support:fix-user-daily-statistics')
            ->setDescription('处理用户每日学习记录的脏数据[脚本为8.7.10新增]：--real 真正覆盖')
            ->addArgument(
                'dataJson',
                InputArgument::REQUIRED,
                '需要更改的数据Json'
            )
            ->addOption(
                'real',
                InputArgument::OPTIONAL
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>开始</info>');
        $this->initServiceKernel();

        $biz = $this->getBiz();
        $real = $input->getOption('real');

        $jsonArray = json_decode($input->getArgument('dataJson'), true);
        foreach ($jsonArray as $item) {
            if (empty($item['userId'])) {
                continue;
            }
            $timestamp = strtotime($item['date']);
            $select = $biz['db']->fetchAssoc('SELECT * FROM `user_learn_statistics_daily` WHERE userId=? AND recordTime=? limit 1;', [$item['userId'], $timestamp]);
            if (empty($select)) {
                $output->writeln("<info>用户#{$item['userId']}在{$item['date']}不存在已有数据，需要新创建</info>");
                if ($real) {
                    $created = $this->getDailyStatisticsDao()->create([
                        'userId' => $item['userId'],
                        'recordTime' => $timestamp,
                        'isStorage' => 0,
                        'learnedSeconds' => $item['learnedTime'],
                    ]);
                    $output->writeln("<info>用户#{$item['userId']}在{$item['date']}创建数据成功：".json_encode($created).'</info>');
                }
                continue;
            }
            $output->writeln("<info>用户#{$item['userId']}在{$item['date']}存在已有数据，学习时间由{$select['learnedSeconds']}改为{$item['learnedTime']}</info>");
            if ($real) {
                $update = $this->getDailyStatisticsDao()->update($select['id'], [
                    'learnedSeconds' => $item['learnedTime'],
                ]);
                $output->writeln("<info>用户#{$item['userId']}在{$item['date']}更新数据成功：".json_encode($update).'</info>');
            }
        }
        $output->writeln('<info>结束</info>');
    }

    /**
     * @return DailyStatisticsDao
     */
    protected function getDailyStatisticsDao()
    {
        return $this->getBiz()->dao('UserLearnStatistics:DailyStatisticsDao');
    }
}
