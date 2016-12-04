<?php

namespace Topxia\WebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class AppBuildMiniVendorCommand extends ContainerAwareCommand
{

    /**
     * project root dir
     * @var string $rootDir
     */
    private $rootDir;

    /**
     * build dir
     * @var string $buildDir
     */
    private $buildDir;

    private $zipName;

    private $vendorDirs = array(
        'asm89/twig-cache-extension/lib',
        'bshaffer/oauth2-server-bundle',
        'bshaffer/oauth2-server-httpfoundation-bridge/src',
        'bshaffer/oauth2-server-php/src',
        'codeages/biz-framework/src',
        'codeages/rest-api-client/src',
        'composer',
        'doctrine/annotations/lib',
        'doctrine/cache/lib',
        'doctrine/collections/lib',
        'doctrine/common/lib',
        'doctrine/dbal/lib',
        'doctrine/doctrine-bundle',
        'doctrine/doctrine-cache-bundle',
        'doctrine/inflector/lib',
        'doctrine/lexer/lib',
        'davedevelopment/phpmig',
        'mockery/mockery/library',
        'doctrine/orm/lib',
        'endroid/qrcode/assets',
        'endroid/qrcode/src',
        'endroid/qrcode-bundle/src',
        'ezyang/htmlpurifier',
        'flexihash/flexihash/src',
        'gregwar/captcha',
        'hamcrest/hamcrest-php',
        'imagine/imagine/lib',
        'incenteev/composer-parameter-handler',
        'ircmaxell/password-compat/lib',
        'jdorn/sql-formatter/lib',
        'kriswallsmith/assetic/src',
        'monolog/monolog/src',
        'paragonie/random_compat',
        'phpoffice/phpexcel/Classes',
        'pimple/pimple/src',
        'psr/log/Psr',
        'sensio/distribution-bundle',
        'sensio/framework-extra-bundle',
        'sensio/generator-bundle',
        'silex/silex/src',
        'swiftmailer/swiftmailer/lib',
        'symfony/assetic-bundle',
        'symfony/monolog-bundle',
        'symfony/phpunit-bridge',
        'symfony/polyfill-apcu',
        'symfony/polyfill-intl-icu',
        'symfony/polyfill-mbstring',
        'symfony/polyfill-php54',
        'symfony/polyfill-php55',
        'symfony/polyfill-php56',
        'symfony/polyfill-php70',
        'symfony/polyfill-util',
        'symfony/monolog-bundle',
        'symfony/security-acl',
        'symfony/swiftmailer-bundle',
        'symfony/symfony/src',
        'twig/twig/lib',
        'willdurand/js-translation-bundle/Bazinga/Bundle/JsTranslationBundle'
    );

    private $unneededFiles = array(
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
        '.gitattributes'
    );

    protected function configure()
    {
        $this
            ->setName('app:build-mini-vendor')
            ->setDescription('制作vendor ZIP包')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rootDir  = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../vendor');
        $this->buildDir = $this->rootDir . '/../build/vendor';

        $fileSystem = new Filesystem();

        if($fileSystem->exists($this->buildDir)){
            $fileSystem->remove($this->buildDir);
        }

        // copy autoload.php
        $fileSystem->mkdir($this->buildDir);
        $fileSystem->copy($this->rootDir . DIRECTORY_SEPARATOR . 'autoload.php', $this->buildDir . DIRECTORY_SEPARATOR . 'autoload.php');


        // copy vendor
        $targetDirs = array_map(function($vendorDir) use ($output, $fileSystem){
            $output->writeln(sprintf('<info>copying %s</info>', $vendorDir));
            $originDir = $this->rootDir . DIRECTORY_SEPARATOR . $vendorDir;
            $targetDir = $this->buildDir . DIRECTORY_SEPARATOR . $vendorDir;
            $fileSystem->mirror($originDir, $targetDir);
            return $targetDir;
        }, $this->vendorDirs);

        // remove unneeded files
        $needRemove = array(
            $this->buildDir . DIRECTORY_SEPARATOR . 'composer/installed.json',
        );
        $finder = new Finder();
        $finder->directories()->in($targetDirs);
        foreach ($finder as $dir){
            $dirName = $dir->getFilename();
            if(lcfirst($dirName) === 'tests'){
                $output->writeln(sprintf('<info>removing unneeded dir %s</info>', $dirName));
                $needRemove[] = $dir->getRealPath();
            }
        }

        $finder = new Finder();
        $finder->files()->ignoreDotFiles(false)->in($targetDirs);
        foreach ($finder as $file){
            if(in_array($file->getFilename(), $this->unneededFiles, true)){
                $output->writeln(sprintf('<info>removing unneeded file %s</info>', $file->getFilename()));
                $needRemove[] = $file->getRealPath();
            }
        }

        $fileSystem->remove($needRemove);
    }
}
