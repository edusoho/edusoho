<?php

namespace Phpmig\Console\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group unit
 */
class InitCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $tempDir;

    public function setUp()
    {
        $this->setTempDir($this->createTempDir());
    }

    public function tearDown()
    {
        $this->cleanUpTempDir($this->getTempDir());
    }

    public function testExecuteCreatesMigrationsDir()
    {
        $command = $this->createCommand();
        $tester = $this->createCommandTester($command);

        $tempDir = $this->getTempDir();
        chdir($tempDir);

        $migrationsDir = $tempDir . DIRECTORY_SEPARATOR . 'migrations';

        $tester->execute(array('command' => $command->getName()));

        $this->assertTrue(file_exists($migrationsDir));
        $this->assertTrue(is_dir($migrationsDir));
    }

    public function testExecuteMigrationDirAlreadyExists()
    {
        $command = $this->createCommand();
        $tester = $this->createCommandTester($command);

        $tempDir = $this->getTempDir();
        chdir($tempDir);

        mkdir($tempDir . DIRECTORY_SEPARATOR . 'migrations');

        $tester->execute(array('command' => $command->getName()));

        $this->assertContains('migrations already exists', $tester->getDisplay());
    }

    public function testExecuteCreatesBootstrapFile()
    {
        $command = $this->createCommand();
        $tester = $this->createCommandTester($command);

        $tempDir = $this->getTempDir();
        chdir($tempDir);

        $tester->execute(array('command' => $command->getName()));

        $this->assertTrue(file_exists($tempDir . DIRECTORY_SEPARATOR . 'phpmig.php'));
    }

    public function testExecuteBootstrapFileExists()
    {
        $command = $this->createCommand();
        $tester = $this->createCommandTester($command);

        $tempDir = $this->getTempDir();
        chdir($tempDir);

        touch($tempDir . DIRECTORY_SEPARATOR . 'phpmig.php');

        $tester->execute(array('command' => $command->getName()));

        $this->assertContains('phpmig.php already exists', $tester->getDisplay());
    }

    /**
     * @return Application
     */
    private function createConsoleApp()
    {
        $app = new Application();
        $app->add(new InitCommand());

        return $app;
    }

    /**
     * @param Command $command
     * @return CommandTester
     */
    private function createCommandTester(Command $command)
    {
        return new CommandTester($command);
    }

    /**
     * @return string
     */
    private function createTempDir()
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(microtime());
        mkdir($tempDir);

        return $tempDir;
    }

    /**
     * @param string $dir
     */
    private function cleanUpTempDir($dir)
    {
        $dh = opendir($dir);

        while (false !== $file = readdir($dh)) {
            if ('.' !== substr($file, 0, 1)) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                    $this->cleanUpTempDir($dir . DIRECTORY_SEPARATOR . $file);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        closedir($dh);
        rmdir($dir);
    }

    /**
     * @param string $tempDir
     */
    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;
    }

    /**
     * @return string
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @return Command
     */
    private function createCommand()
    {
        $app = $this->createConsoleApp();

        return $app->find('init');
    }
}