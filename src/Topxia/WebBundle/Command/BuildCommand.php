<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BuildCommand extends BaseCommand
{

	protected function configure()
	{
		$this->setName ( 'topxia:build' );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Start build.</info>');
		$this->initBuild($input, $output);

		$this->buildAppDirectory();
		$this->buildDocDirectory();
		$this->buildSrcDirectory();
		$this->buildVendorDirectory();
		$this->buildWebDirectory();

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
		$this->distDirectory = $this->buildDirectory . '/edusoho';
		$this->filesystem->mkdir($this->distDirectory);
	}

	private function package()
	{
		$this->output->writeln('packaging...');

		chdir($this->buildDirectory);
		$command = "tar czvf edusoho-1.0.tar.gz edusoho/";
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
		$this->filesystem->mkdir("{$this->distDirectory}/app/cache");
		$this->filesystem->mkdir("{$this->distDirectory}/app/data");
		$this->filesystem->mkdir("{$this->distDirectory}/app/logs");
		$this->filesystem->mirror("{$this->rootDirectory}/app/Resources", "{$this->distDirectory}/app/Resources");
		$this->filesystem->mirror("{$this->rootDirectory}/app/config", "{$this->distDirectory}/app/config");

		$this->filesystem->chmod("{$this->distDirectory}/app/cache", 0777);
		$this->filesystem->chmod("{$this->distDirectory}/app/data", 0777);
		$this->filesystem->chmod("{$this->distDirectory}/app/logs", 0777);

		$this->filesystem->copy("{$this->distDirectory}/app/config/parameters.yml.dist", "{$this->distDirectory}/app/config/parameters.yml");
		$this->filesystem->chmod("{$this->distDirectory}/app/config/parameters.yml", 0777);

		$this->filesystem->remove("{$this->distDirectory}/app/config/config_dev.yml");
		$this->filesystem->remove("{$this->distDirectory}/app/config/config_test.yml");
		$this->filesystem->remove("{$this->distDirectory}/app/config/parameters.yml.dist");
		$this->filesystem->remove("{$this->distDirectory}/app/config/routing_dev.yml");

	}

	public function buildDocDirectory()
	{
		$this->output->writeln('build doc/ .');

		$this->filesystem->mkdir("{$this->distDirectory}/doc");
		$this->filesystem->copy("{$this->rootDirectory}/doc/INSTALL.md", "{$this->distDirectory}/doc/INSTALL.md", true);
	}

	public function buildSrcDirectory()
	{
		$this->output->writeln('build src/ .');
		$this->filesystem->mirror("{$this->rootDirectory}/src", "{$this->distDirectory}/src");

		$this->filesystem->remove("{$this->distDirectory}/src/Topxia/AdminBundle/Resources/public");
		$this->filesystem->remove("{$this->distDirectory}/src/Topxia/WebBundle/Resources/public");

		$finder = new Finder();
		$finder->directories()->in("{$this->distDirectory}/src/");

		$toDeletes = array();
		foreach ($finder as $dir) {
			if ($dir->getFilename() == 'Tests') {
				$toDeletes[] = $dir->getRealpath();
			}
		}

		foreach ($toDeletes as $file) {
			$this->filesystem->remove($file);
		}

	}

	public function buildVendorDirectory()
	{
		$this->output->writeln('build vendor/ .');
		$this->filesystem->mkdir("{$this->distDirectory}/vendor");
		$this->filesystem->copy("{$this->rootDirectory}/vendor/autoload.php", "{$this->distDirectory}/vendor/autoload.php");

		$directories = array(
			'composer',
			'doctrine/common/lib/Doctrine',
			'doctrine/dbal/lib/Doctrine',
			'doctrine/doctrine-bundle',
			'ezyang/htmlpurifier/library',
			'imagine/imagine/lib',
			'jdorn/sql-formatter/lib',
			'kriswallsmith/assetic/src',
			'monolog/monolog/src',
			'psr/log/Psr',
			'sensio/framework-extra-bundle',
			'swiftmailer/swiftmailer/lib',
			'symfony/assetic-bundle',
			'symfony/icu',
			'symfony/monolog-bundle',
			'symfony/swiftmailer-bundle',
			'symfony/symfony/src'
		);

		foreach ($directories as $dir) {
			$this->filesystem->mirror("{$this->rootDirectory}/vendor/{$dir}", "{$this->distDirectory}/vendor/{$dir}");
		}

		$this->filesystem->remove("{$this->distDirectory}/vendor/composer/installed.json");

		$finder = new Finder();
		$finder->directories()->in("{$this->distDirectory}/vendor");

		$toDeletes = array();
		foreach ($finder as $dir) {
			if ($dir->getFilename() == 'Tests') {
				$toDeletes[] = $dir->getRealpath();
			}
		}

		$this->filesystem->remove($toDeletes);

	}

	public function buildWebDirectory()
	{
		$this->output->writeln('build web/ .');

		$this->filesystem->mkdir("{$this->distDirectory}/web");
		$this->filesystem->mkdir("{$this->distDirectory}/web/bundles");
		$this->filesystem->mirror("{$this->rootDirectory}/web/assets", "{$this->distDirectory}/web/assets");
		$this->filesystem->mirror("{$this->rootDirectory}/web/customize", "{$this->distDirectory}/web/customize");
		$this->filesystem->mirror("{$this->rootDirectory}/web/install", "{$this->distDirectory}/web/install");

		$this->filesystem->copy("{$this->rootDirectory}/web/.htaccess", "{$this->distDirectory}/web/.htaccess");
		$this->filesystem->copy("{$this->rootDirectory}/web/app.php", "{$this->distDirectory}/web/app.php");
		$this->filesystem->copy("{$this->rootDirectory}/web/favicon.ico", "{$this->distDirectory}/web/favicon.ico");
		$this->filesystem->copy("{$this->rootDirectory}/web/robots.txt", "{$this->distDirectory}/web/robots.txt");


		$finder = new Finder();
		$finder->files()->in("{$this->distDirectory}/web/assets/libs");
		foreach ($finder as $file) {
			$filename = $file->getFilename();
			if ($filename == 'package.json' or preg_match('/-debug.js$/', $filename) or preg_match('/-debug.css$/', $filename)) {
				$this->filesystem->remove($file->getRealpath());
			}
		}

		$finder = new Finder();
		$finder->directories()->in("{$this->rootDirectory}/web/bundles")->depth('== 0');
		foreach ($finder as $dir) {
			$this->filesystem->mirror($dir->getRealpath(), "{$this->distDirectory}/web/bundles/{$dir->getFilename()}");
		}

	}

}