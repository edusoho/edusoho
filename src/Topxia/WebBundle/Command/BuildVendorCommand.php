<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Topxia\System;
use Topxia\Common\BlockToolkit;


class BuildVendorCommand extends BaseCommand
{

	protected function configure()
	{
		$this->setName ( 'topxia:build-vendor' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Start build.</info>');
		$this->initBuild($input, $output);
		$this->buildAppDirectory();
		$this->buildVendorDirectory();
		$this->cleanMacosDirectory();

		$this->package();

		$this->clean();

		$output->writeln('<info>End build.</info>');

		// $filesystem->mirror("{$rootDirectory}/{$directory}", "{$targetDirectory}/{$directory}");
	}

	private function initBuild(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		$this->output = $output;

		$this->rootDirectory = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../');
		$this->buildDirectory = $this->rootDirectory . '/build';

		$this->filesystem = new Filesystem();

		if ($this->filesystem->exists($this->buildDirectory)) {
			$this->filesystem->remove($this->buildDirectory);
		}
		$this->distDirectory = $this->buildDirectory . '/vendor2';
		$this->filesystem->mkdir($this->distDirectory);
	}

	private function package()
	{
		$this->output->writeln('packaging...');

		chdir($this->buildDirectory);

		$command = "zip -r vendor-" . System::VERSION . ".zip vendor2/";
		exec($command);
	}

	private function clean()
	{
		$this->output->writeln('cleaning...');

	}

	private function buildAppDirectory()
	{
		$this->output->writeln('build app/ .');

		$this->filesystem->mkdir("{$this->distDirectory}/app");

		$this->filesystem->copy("{$this->rootDirectory}/app/autoload.php", "{$this->distDirectory}/app/autoload.php");
		$this->filesystem->copy("{$this->rootDirectory}/app/bootstrap.php.cache", "{$this->distDirectory}/app/bootstrap.php.cache");

	}

	public function buildVendorDirectory()
	{
		$this->output->writeln('build vendor2/ .');
		$this->filesystem->mkdir("{$this->distDirectory}/vendor2");
		$this->filesystem->copy("{$this->rootDirectory}/vendor/autoload.php", "{$this->distDirectory}/vendor2/autoload.php");

		$directories = array(
			'composer',
			'doctrine/annotations/lib',
			'doctrine/cache/lib',
			'doctrine/collections/lib',
			'doctrine/common/lib/Doctrine',
			'doctrine/dbal/lib/Doctrine',
			'doctrine/doctrine-bundle',
			'doctrine/doctrine-cache-bundle',
			'doctrine/doctrine-migrations-bundle',
			'doctrine/inflector/lib',
			'doctrine/lexer/lib',
			'doctrine/migrations/lib',
			'doctrine/orm/lib',
			'endroid/qrcode/src',
			'endroid/qrcode/assets',
			'ezyang/htmlpurifier/library',
			'gregwar/captcha',
			'imagine/imagine/lib',
			'incenteev/composer-parameter-handler',
			'jdorn/sql-formatter/lib',
			'kriswallsmith/assetic/src',
			'monolog/monolog/src',
			'phpoffice/phpexcel/Classes',
			'pimple/pimple/lib',
			'psr/log/Psr',
			'sensio/distribution-bundle',
			'sensio/framework-extra-bundle',
			'sensio/generator-bundle',
			'sensiolabs/security-checker',
			'silex/silex/src',
			'swiftmailer/swiftmailer/lib',
			'symfony/assetic-bundle',
			'symfony/monolog-bundle',
			'symfony/swiftmailer-bundle',
			'symfony/symfony/src',
			'twig/extensions/lib',
			'twig/twig/lib',
		);

		foreach ($directories as $dir) {
			$this->filesystem->mirror("{$this->rootDirectory}/vendor/{$dir}", "{$this->distDirectory}/vendor2/{$dir}");
		}

		$this->filesystem->remove("{$this->distDirectory}/vendor2/composer/installed.json");

		$finder = new Finder();
		$finder->directories()->in("{$this->distDirectory}/vendor2");

		$toDeletes = array();
		foreach ($finder as $dir) {
			if ($dir->getFilename() == 'Tests') {
				$toDeletes[] = $dir->getRealpath();
			}
		}

		$this->filesystem->remove($toDeletes);

		$remainFiles = array(
			'composer/LICENSE',
			'doctrine/annotations/LICENSE',
			'doctrine/cache/LICENSE',
			'doctrine/collections/LICENSE',
			'doctrine/common/LICENSE',
			'doctrine/dbal/LICENSE',
			'doctrine/doctrine-bundle/LICENSE',
			'doctrine/inflector/LICENSE',
			'doctrine/lexer/LICENSE',
			'doctrine/migrations/LICENSE',
			'doctrine/orm/LICENSE',
			'endroid/qrcode/LICENSE',
			'ezyang/htmlpurifier/LICENSE',
			'gregwar/captcha/LICENSE',
			'imagine/imagine/LICENSE',
			'incenteev/composer-parameter-handler/LICENSE',
			'jdorn/sql-formatter/LICENSE.txt',
			'kriswallsmith/assetic/LICENSE',
			'monolog/monolog/LICENSE',
			'phpoffice/phpexcel/license.md',
			'pimple/pimple/LICENSE',
			'psr/log/LICENSE',
			'sensiolabs/security-checker/LICENSE',
			'silex/silex/LICENSE',
			'swiftmailer/swiftmailer/LICENSE',
			'symfony/assetic-bundle/LICENSE',
			'symfony/symfony/LICENSE',
		);

		foreach ($remainFiles as $file) {
			// $path = "{$this->rootDirectory}/vendor/{$file}";

			// if (!file_exists($path)) {
			// 	echo $path . "\n";
			// }


			$this->filesystem->copy("{$this->rootDirectory}/vendor/{$file}", "{$this->distDirectory}/vendor2/{$file}");
		}

	}

	public function cleanMacosDirectory()
	{
		$finder = new Finder();
		$finder->files()->in($this->distDirectory)->ignoreDotFiles(false);
		foreach ($finder as $dir) {

			if ($dir->getBasename() == '.DS_Store') {
				$this->filesystem->remove($dir->getRealpath());
			}
		}
	}

}