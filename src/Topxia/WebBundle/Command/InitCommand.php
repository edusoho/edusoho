<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends BaseCommand
{

	protected function configure()
	{
		$this->setName ( 'topxia:init' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('文件设置初始化');

		$fileSettings = array(
			'public_directory' => 'web/files',
			'public_web_path' => '/files',
			'private_directory' => 'private_files',
		);

		$this->getSettingService()->set('file', $fileSettings);
	}

	protected function getSettingService()
	{
		return $this->getServiceKernel()->createService('System.SettingService');
	}

}

