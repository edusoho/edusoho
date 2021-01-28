<?php

namespace Phpmig\Migration;

use Mockery as m;
use Phpmig\Migration\Migration;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OutputInterface|m\MockInterface
     */
    private $migrationOutput;

    /**
     * @var DialogHelper|m\MockInterface
     */
    private $migrationDialogHelper;

    /**
     * @var Migration
     */
    private $migration;

    public function setup()
    {
        $this->migrationOutput = m::mock('Symfony\Component\Console\Output\OutputInterface')->shouldIgnoreMissing();
        $this->migrationDialogHelper = m::mock('Symfony\Component\Console\Helper\DialogHelper')->shouldIgnoreMissing();

        $this->migration = new Migration(1);
        $this->migration->setOutput($this->migrationOutput);
        $this->migration->setDialogHelper($this->migrationDialogHelper);
    }

    /**
     * @test
     */
    public function shouldAskForInput()
    {
        $this->migrationDialogHelper->shouldReceive('ask')
            ->with($this->migrationOutput, $question = 'Wat?', $default = 'huh?')
            ->andReturn($ans = 'dave')
            ->once();

        $this->assertEquals($ans, $this->migration->ask($question, $default));
    }

    /**
     * @test
     */
    public function shouldAskForConfirmation()
    {
        $this->migrationDialogHelper->shouldReceive('askConfirmation')
            ->with($this->migrationOutput, $question = 'Wat?', $default = true)
            ->andReturn($ans = 'dave')
            ->once();

        $this->assertEquals($ans, $this->migration->confirm($question, $default));
    }

    /**
     * @test
     */
    public function shouldRetrieveServices()
    {
        $this->migration->setContainer(new \ArrayObject(array('service' => 123)));
        $this->assertEquals(123, $this->migration->get('service'));
    }

    public function testMigrationVersion()
    {
        $this->assertEquals(1, $this->migration->getVersion());
    }

}
