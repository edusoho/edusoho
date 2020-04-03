<?php

namespace Incenteev\ParameterHandler\Tests;

use Incenteev\ParameterHandler\Processor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class ProcessorTest extends TestCase
{
    private $io;
    private $environmentBackup = array();

    /**
     * @var Processor
     */
    private $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->io = $this->prophesize('Composer\IO\IOInterface');
        $this->processor = new Processor($this->io->reveal());
    }

    protected function tearDown()
    {
        parent::tearDown();

        foreach ($this->environmentBackup as $var => $value) {
            if (false === $value) {
                putenv($var);
            } else {
                putenv($var.'='.$value);
            }
        }
    }

    /**
     * @dataProvider provideInvalidConfiguration
     */
    public function testInvalidConfiguration(array $config, $exceptionMessage)
    {
        chdir(__DIR__);

        if (method_exists($this, 'expectException')) {
            $this->expectException('InvalidArgumentException');
            $this->expectExceptionMessage($exceptionMessage);
        } else {
            $this->setExpectedException('InvalidArgumentException', $exceptionMessage);
        }

        $this->processor->processFile($config);
    }

    public function provideInvalidConfiguration()
    {
        return array(
            'no file' => array(
                array(),
                'The extra.incenteev-parameters.file setting is required to use this script handler.',
            ),
            'missing default dist file' => array(
                array(
                    'file' => 'fixtures/invalid/missing.yml',
                ),
                'The dist file "fixtures/invalid/missing.yml.dist" does not exist. Check your dist-file config or create it.',
            ),
            'missing custom dist file' => array(
                array(
                    'file' => 'fixtures/invalid/missing.yml',
                    'dist-file' => 'fixtures/invalid/non-existent.dist.yml',
                ),
                'The dist file "fixtures/invalid/non-existent.dist.yml" does not exist. Check your dist-file config or create it.',
            ),
            'missing top level key in dist file' => array(
                array(
                    'file' => 'fixtures/invalid/missing_top_level.yml',
                ),
                'The top-level key parameters is missing.',
            ),
            'invalid values in the existing file' => array(
                array(
                    'file' => 'fixtures/invalid/invalid_existing_values.yml',
                ),
                'The existing "fixtures/invalid/invalid_existing_values.yml" file does not contain an array',
            ),
        );
    }

    /**
     * @dataProvider provideParameterHandlingTestCases
     */
    public function testParameterHandling($testCaseName)
    {
        $dataDir = __DIR__.'/fixtures/testcases/'.$testCaseName;

        $testCase = array_replace_recursive(
            array(
                'title' => 'unknown test',
                'config' => array(
                    'file' => 'parameters.yml',
                ),
                'dist-file' => 'parameters.yml.dist',
                'environment' => array(),
                'interactive' => false,
            ),
            (array) Yaml::parse(file_get_contents($dataDir.'/setup.yml'))
        );

        $workingDir = sys_get_temp_dir() . '/incenteev_parameter_handler';
        $exists = $this->initializeTestCase($testCase, $dataDir, $workingDir);

        $message = sprintf('<info>%s the "%s" file</info>', $exists ? 'Updating' : 'Creating', $testCase['config']['file']);
        $this->io->write($message)->shouldBeCalled();

        $this->setInteractionExpectations($testCase);

        $this->processor->processFile($testCase['config']);

        $this->assertFileEquals($dataDir.'/expected.yml', $workingDir.'/'.$testCase['config']['file'], $testCase['title']);
    }

    private function initializeTestCase(array $testCase, $dataDir, $workingDir)
    {
        $fs = new Filesystem();

        if (is_dir($workingDir)) {
            $fs->remove($workingDir);
        }

        $fs->copy($dataDir.'/dist.yml', $workingDir.'/'. $testCase['dist-file']);

        if ($exists = file_exists($dataDir.'/existing.yml')) {
            $fs->copy($dataDir.'/existing.yml', $workingDir.'/'.$testCase['config']['file']);
        }

        foreach ($testCase['environment'] as $var => $value) {
            $this->environmentBackup[$var] = getenv($var);
            putenv($var.'='.$value);
        };

        chdir($workingDir);

        return $exists;
    }

    private function setInteractionExpectations(array $testCase)
    {
        $this->io->isInteractive()->willReturn($testCase['interactive']);

        if (!$testCase['interactive']) {
            return;
        }

        if (!empty($testCase['requested_params'])) {
            $this->io->write('<comment>Some parameters are missing. Please provide them.</comment>')->shouldBeCalledTimes(1);
        }

        foreach ($testCase['requested_params'] as $param => $settings) {
            $this->io->ask(sprintf('<question>%s</question> (<comment>%s</comment>): ', $param, $settings['default']), $settings['default'])
                ->willReturn($settings['input'])
                ->shouldBeCalled();
        }
    }

    public function provideParameterHandlingTestCases()
    {
        $tests = array();

        foreach (glob(__DIR__.'/fixtures/testcases/*/') as $folder) {
            $tests[] = array(basename($folder));
        }

        return $tests;
    }
}
