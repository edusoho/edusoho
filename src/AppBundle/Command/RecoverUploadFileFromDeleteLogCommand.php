<?php

namespace AppBundle\Command;

use Biz\File\Dao\UploadFileDao;
use Biz\System\Service\LogService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RecoverUploadFileFromDeleteLogCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:recover-upload-file')
            ->addArgument('after', InputArgument::REQUIRED, '起始时间 like 2024-08-11')
            ->addArgument('before', InputArgument::REQUIRED, '结束时间 like 2024-08-12')
            ->addOption('real', null, InputOption::VALUE_NONE, '是否执行');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logs = $this->getLogService()->searchLogs([
            'module' => 'upload_file',
            'action' => 'delete',
            'startDateTime' => $input->getArgument('after'),
            'endDateTime' => $input->getArgument('before'),
        ], [], 0, PHP_INT_MAX);
        if (empty($logs)) {
            $output->writeln('<info>没有可恢复的文件</info>');

            return;
        }
        $output->writeln('<info>查询到' . count($logs) . '条删除记录</info>');
        $existFiles = $this->getUploadFileDao()->findByIds(array_column(array_column($logs, 'data'), 'id'));
        $existFiles = array_column($existFiles, null, 'id');
        $output->writeln('<info>可恢复的文件: </info>');
        $files = [];
        foreach ($logs as $log) {
            if (!empty($existFiles[$log['data']['id']])) {
                continue;
            }
            $output->writeln("<info>{$log['data']['filename']}</info>");
            $files[] = $log['data'];
        }
        if (empty($files)) {
            $output->writeln('<info>没有可恢复的文件</info>');

            return;
        }
        if ($input->getOption('real')) {
            $this->getUploadFileDao()->batchCreate($files);
            $output->writeln('<info>恢复文件成功</info>');
        }
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return UploadFileDao
     */
    private function getUploadFileDao()
    {
        return $this->getBiz()->dao('File:UploadFileDao');
    }
}
