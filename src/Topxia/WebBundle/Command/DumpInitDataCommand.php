<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Topxia\Service\User\CurrentUser;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

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
            )->setName ( 'topxia:dump-init-data' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>dump-init-sql开始</info>');
		$domain = $input->getArgument('domain');
		$user = $input->getArgument('user');
		$password = $input->getArgument('password');
		$database = $input->getArgument('database');

		$time = time();

		$command = "ssh -l root {$domain} 'mysqldump -u{$user} -p{$password} {$database} --no-create-info --complete-insert --skip-comments --extended-insert --skip-add-locks --ignore-table={$database}.cache --ignore-table={$database}.cloud_app_logs --ignore-table={$database}.log --ignore-table={$database}.session2 --ignore-table={$database}.user_token --skip-disable-keys --skip-set-charset --skip-tz-utc --skip-debug-check > edusoho_init.{$time}.sql'";
		$output->writeln("<info>{$command}</info>");
		exec($command);

		$rootPath = __DIR__.'/../../../..';
		$filesystem = new Filesystem();
		if (!$filesystem->exists("{$rootPath}/installFiles")) {
			$filesystem->mkdir("{$rootPath}/installFiles");
		}

		$command = "scp root@{$domain}:~/edusoho_init.{$time}.sql {$rootPath}/installFiles/edusoho_init.sql";
		$output->writeln("<info>{$command}</info>");
		exec($command);

		$command = "cp {$rootPath}/installFiles/edusoho_init.sql {$rootPath}/web/install/edusoho_init.sql";
		$output->writeln("<info>{$command}</info>");
		exec($command);

		$command = "ssh -l root {$domain} 'cd /var/www/{$domain} \n zip -r ~/data.{$time}.zip app/data/private_files app/data/udisk'";
		$output->writeln("<info>{$command}</info>");
		exec($command);

		$command = "scp root@{$domain}:~/data.{$time}.zip {$rootPath}/installFiles/data.zip";
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