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

class CopyInstallFilesCommand extends BaseCommand
{
	protected function configure()
	{
		$this->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'version?'
            )->setName ( 'topxia:copy-install-files' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>copy-install-files开始</info>');
		$version = $input->getArgument('version');
		
		$command = "rm -rf build/edusoho-{$version}.tar.gz";
		$output->writeln("<info>{$command}</info>");
		exec($command);

		$command = "cp -r installFiles/data/* build/edusoho/.";
		$output->writeln("<info>{$command}</info>");
		exec($command);

		$command = "cd build \n tar -czf edusoho-{$version}.tar.gz edusoho/";
		$output->writeln("<info>{$command}</info>");
		exec($command);

		$output->writeln('<info>copy-install-files结束</info>');
	}
}