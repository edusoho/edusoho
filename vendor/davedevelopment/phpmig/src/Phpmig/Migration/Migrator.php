<?php
/**
 * @package    Phpmig
 * @subpackage Phpmig\Migration
 */
namespace Phpmig\Migration;

use Phpmig\Adapter\AdapterInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * This file is part of phpmig
 *
 * Copyright (c) 2011 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Migrator
 *
 * Decided what to migrate and migrates
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class Migrator
{
    /**
     * @var \ArrayAccess
     */
    protected $container = null;

    /**
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * Constructor
     *
     * @param AdapterInterface $adapter
     * @param \ArrayAccess $container
     */
    public function __construct(AdapterInterface $adapter, \ArrayAccess $container, OutputInterface $output)
    {
        $this->container  = $container;
        $this->adapter    = $adapter;
        $this->output     = $output;
    }

    /**
     * Run the up method on a migration
     *
     * @param Migration $migration
     * @return void
     */
    public function up(Migration $migration)
    {
        $this->run($migration, 'up');
        return;
    }

    /**
     * Run the down method on a migration
     *
     * @param Migration $migration
     * @return void
     */
    public function down(Migration $migration)
    {
        $this->run($migration, 'down');
        return;
    }

    /**
     * Run a migration in a particular direction
     *
     * @param Migration $migration
     * @param string $direction
     * @return void
     */
    protected function run(Migration $migration, $direction = 'up')
    {
        $direction = ($direction == 'down' ? 'down' :'up');
        $this->getOutput()->writeln(sprintf(
            ' == <info>' .
            $migration->getVersion() . ' ' .
            $migration->getName() . '</info> ' .
            '<comment>' .
            ($direction == 'up' ? 'migrating' : 'reverting') .
            '</comment>'
        ));
        $start = microtime(1);
        $migration->setContainer($this->getContainer());
        $migration->init();
        $migration->{$direction}();
        $this->getAdapter()->{$direction}($migration);
        $end = microtime(1);
        $this->getOutput()->writeln(sprintf(
            ' == <info>' .
            $migration->getVersion() . ' ' .
            $migration->getName() . '</info> ' .
            '<comment>' .
            ($direction == 'up' ? 'migrated ' : 'reverted ') .
            sprintf("%.4fs", $end - $start) .
            '</comment>'
        ));
    }

    /**
     * Get Container
     *
     * @return \ArrayAccess
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set Container
     *
     * @param \ArrayAccess $container
     * @return Migrator
     */
    public function setContainer(\ArrayAccess $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Get Adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set Adapter
     *
     * @param AdapterInterface $adapter
     * @return Migrator
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Get Output
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set Output
     *
     * @param OutputInterface $output
     * @return Migrator
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }
}



