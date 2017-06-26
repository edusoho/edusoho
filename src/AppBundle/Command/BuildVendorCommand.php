<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BuildVendorCommand extends ContainerAwareCommand
{
    /**
     * project root dir.
     *
     * @var string
     */
    public $rootDir;

    /**
     * build dir.
     *
     * @var string
     */
    public $buildDir;

    public $unneededFiles = array(
        'appveyor.yml',
        'CONTRIBUTING.md',
        'CONTRIBUTORS.md',
        'phpunit',
        '.gitignore',
        '.travis.yml',
        'CHANGELOG.md',
        'composer.json',
        'phpunit.xml.dist',
        'Gemfile',
        'README.md',
        'UPGRADE.md',
        'VERSION',
        'CHANGES',
        '.gitattributes',
        '.DS_Store',
    );

    protected function configure()
    {
        $this
            ->setName('build:vendor')
            ->setDescription('制作vendor ZIP包')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();
        $finder = new Finder();
        $biz = $this->getContainer()->get('biz');

        $rootDir = $biz['kernel.root_dir'].'/../';
        $originDir =  $rootDir. 'vendor/';
        $buildVendorDir = $rootDir.'build/edusoho/vendor/';

        if ($fileSystem->exists($buildVendorDir)) {
            $fileSystem->remove($buildVendorDir);
        }else{
            $fileSystem->mkdir($buildVendorDir);
        }

        $output->writeln('remove develop bundle using command: composer install --no-dev');
        chdir($rootDir);
        $fileSystem->remove($rootDir.'vendor/codeages/plugin-bundle/Command/PluginCreateCommand.php');
        $fileSystem->remove(rootDir. 'src/AppBundle/Command/OldPluginCreateCommand.php');
        exec('composer install --no-dev');


        $finder->depth('== 1')->in($originDir);

        foreach ($finder as $folder){
            $fileName = $folder->getFilename();
            if( $folder->isFile()){
                $paths = explode('vendor',  $folder->getRealPath());
                $fileSystem->copy($folder->getRealPath(), $buildVendorDir.$paths[1]);
                $output->writeln('build vendor/'. $buildVendorDir.$paths[1]);
            }else if( !in_array($folder->getRelativePath(), $this->ignoreDeveloperFolders())){
                $path = $folder->getRelativePath(). '/'. $fileName;
                if(in_array($path, $this->ignoreDeveloperFolders())){
                    $output->writeln('ignore vendor/'. $path);
                }else{
                    $fileSystem->mirror($folder->getRealPath(), $buildVendorDir.$path);
                    $output->writeln('build vendor/'. $path);
                }
            }
        }
        $fileSystem->copy($originDir.'autoload.php', $buildVendorDir.'autoload.php');
        $output->writeln('recovery bundle using command: composer install');
        exec('composer install');
        exec('git checkout -- vendor');

    }

    private function ignoreDeveloperFolders(){
        return array(
            'codeception/codeception',
            'behat/gherkin',
            'facebook/webdriver',
            'guzzlehttp/guzzle',
            'guzzlehttp/promises',
            'guzzlehttp/psr7',
            'psr/http-message',
            'phpunit/phpunit',
            'phpspec/prophecy',
            'phpdocumentor/reflection-docblock',
            'phpdocumentor/type-resolver',
            'phpdocumentor/reflection-common',
            'webmozart/assert',
            'phpunit/php-code-coverage',
            'phpunit/php-token-stream',
            'phpunit/php-file-iterator',
            'phpunit/php-timer',
            'sebastian/environment',
            'sebastian/global-state',
            'sebastian/version',
            'phpunit/phpunit-mock-objects',
            'phpunit/php-text-template',
            'doctrine/instantiator',
            'sebastian/comparator',
            'sebastian/exporter',
            'sebastian/recursion-context',
            'sebastian/diff',
            'stecman/symfony-console-completion',
            'mockery/mockery',
            'hamcrest/hamcrest-php',
            'symfony/phpunit-bridge',
            'sensio/generator-bundle',
        );
    }
}
