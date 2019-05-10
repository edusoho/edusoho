<?php

namespace AppBundle\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpInitDataCommand extends BaseCommand
{
    protected function configure()
    {
        $this->addArgument(
            'domain',
            InputArgument::OPTIONAL,
            '服务器地址?'
        )->addArgument(
            'user',
            InputArgument::OPTIONAL,
            '数据库用户名?'
        )->addArgument(
            'password',
            InputArgument::OPTIONAL,
            '数据库密码?'
        )->addArgument(
            'database',
            InputArgument::OPTIONAL,
            '数据库?'
        )->addArgument(
            'projectPath',
            InputArgument::OPTIONAL,
            '演示站项目路径'
        )->setName('topxia:dump-init-data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>dump-init-sql开始</info>');
        $domain = $input->getArgument('domain');
        $user = $input->getArgument('user');
        $password = $input->getArgument('password');
        $database = $input->getArgument('database');
        $projectPath = $input->getArgument('projectPath');
        $projectPath = empty($projectPath) ? '/var/www/edusoho' : $projectPath;

        $domain = explode(':', $domain);
        $host = $domain[0];
        $port = empty($domain[1]) ? 22 : $domain[1];
        $time = time();

        $command = "ssh -l root {$host} -p {$port} \
        'mysqldump -u{$user} -p {$database} \
        --no-create-info --complete-insert \
        --skip-comments --extended-insert \
        --skip-add-locks --ignore-table={$database}.cache \
        --ignore-table={$database}.cloud_app_logs \
        --ignore-table={$database}.sessions \
        --ignore-table={$database}.log \
        --ignore-table={$database}.session2 \
        --ignore-table={$database}.user_token \
        --ignore-table={$database}.status \
        --ignore-table={$database}.log_v8 \
        --ignore-table={$database}.biz_scheduler_job_fired \
        --ignore-table={$database}.biz_scheduler_job_log \
        --ignore-table={$database}.biz_scheduler_job_process \
        --ignore-table={$database}.biz_targetlog \
        --ignore-table={$database}.xapi_activity_watch_log \
        --ignore-table={$database}.xapi_statement \
        --ignore-table={$database}.xapi_statement_archive \
        --skip-disable-keys --skip-set-charset \
        --skip-tz-utc > edusoho_init.{$time}.sql'";

        $output->writeln("<info>{$command}</info>");
        exec($command);

        $command = "ssh -l root {$host} -p {$port} \"mysqldump -u{$user} -p -d {$database} --compact --add-drop-table | sed 's/ AUTO_INCREMENT=[0-9]*//g' > edusoho_structure.{$time}.sql\"";

        $output->writeln("<info>{$command}</info>");
        exec($command);

        $rootPath = __DIR__.'/../../..';
        $filesystem = new Filesystem();

        if (!$filesystem->exists("{$rootPath}/installFiles")) {
            $filesystem->mkdir("{$rootPath}/installFiles");
        }

        $command = "scp -P {$port} root@{$host}:~/edusoho_init.{$time}.sql {$rootPath}/installFiles/edusoho_init.sql";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $command = "scp -P {$port} root@{$host}:~/edusoho_structure.{$time}.sql {$rootPath}/installFiles/edusoho_structure.sql";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $command = "cp {$rootPath}/installFiles/edusoho_init.sql {$rootPath}/web/install/edusoho_init.sql";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $command = "cp {$rootPath}/installFiles/edusoho_structure.sql {$rootPath}/web/install/edusoho.sql";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $command = "ssh -l root {$host} -p {$port} 'cd {$projectPath} \n zip -r ~/data.{$time}.zip app/data/private_files app/data/udisk web/files'";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $command = "scp -P {$port} root@{$host}:~/data.{$time}.zip {$rootPath}/installFiles/data.zip";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $command = "rm -rf {$rootPath}/installFiles/data";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $command = "unzip -d {$rootPath}/installFiles/data {$rootPath}/installFiles/data.zip";
        $output->writeln("<info>{$command}</info>");
        exec($command);

        $output->writeln('<info>dump-init-sql结束</info>');
    }
}
