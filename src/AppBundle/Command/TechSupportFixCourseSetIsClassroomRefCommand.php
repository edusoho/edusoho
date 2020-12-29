<?php

namespace AppBundle\Command;

use Biz\Course\Dao\CourseSetDao;
use Biz\System\Dao\LogDao;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TechSupportFixCourseSetIsClassroomRefCommand extends BaseCommand
{
    public function configure()
    {
        /*
         * [
         *  {"userId":"1", "date":"2020-06-24", "learnedTime":"3600"},
         *  ...
         * ]
         */
        $this->setName('tech-support:fix-course-set-is-classroom-ref')
            ->setDescription('处理解除课程班级课程所属分类出问题[脚本为20.4.9新增]：--real 真正覆盖')
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

        $logs = $this->getLogDao()->search(['module' => 'classroom', 'action' => 'delete_course'], [], 0, PHP_INT_MAX);
        foreach ($logs as $log) {
            $output->writeln("<info>{$log['message']}, 开始处理。。。</info>");
            $data = json_decode($log['data'], true);
            if ($real) {
                $this->getCourseSetDao()->update($data['courseSetId'], ['isClassroomRef' => 1]);
            }
        }
        $output->writeln('<info>待更新班级内课程的字段更新</info>');
        if ($real) {
            $output->writeln('<info>开始更新班级内课程的字段更新</info>');
            $biz['db']->exec('update `course_set_v8` set `isClassroomRef` = 1 where `parentId` > 0;');
        }

        $output->writeln('<info>结束</info>');
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->getBiz()->dao('Course:CourseSetDao');
    }

    /**
     * @return LogDao
     */
    protected function getLogDao()
    {
        return $this->getBiz()->dao('System:LogDao');
    }
}
