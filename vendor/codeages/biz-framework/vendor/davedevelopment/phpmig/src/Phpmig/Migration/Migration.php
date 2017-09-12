<?php
/**
 * @package    Phpmig
 * @subpackage Phpmig\Migration
 */
namespace Phpmig\Migration;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

/**
 * This file is part of phpmig
 *
 * Copyright (c) 2011 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Migration
 *
 * A migration describes the changes that should be made (or unmade)
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class Migration
{
    /**
     * @var int
     */
    protected $version = null;

    /**
     * @var \ArrayAccess
     */
    protected $container = null;

    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * @var DialogHelper
     */
    protected $dialogHelper = null;

    /**
     * Constructor
     *
     * @param int $version
     */
    final public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        return;
    }

    /**
     * Do the migration
     *
     * @return void
     */
    public function up()
    {
        return;
    }

    /**
     * Undo the migration
     *
     * @return void
     */
    public function down()
    {
        return;
    }

    /**
     * Get Version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param int $version
     * @return Migration
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return get_class($this);
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

    /**
     * Ask for input
     *
     * @param string $question
     * @param mixed $default
     * @return string The users answer
     */
    public function ask($question, $default = null)
    {
        return $this->getDialogHelper()->ask($this->getOutput(), $question, $default);
    }

    /**
     * Ask for confirmation
     *
     * @param string $question
     * @param mixed $default
     * @return string The users answer
     */
    public function confirm($question, $default = true)
    {
        return $this->getDialogHelper()->askConfirmation($this->getOutput(), $question, $default);
    }

    /**
     * Get something from the container
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $c = $this->getContainer();
        return $c[$key];
    }

    /**
     * Get Dialog Helper
     *
     * @return DialogHelper
     */
    public function getDialogHelper()
    {
        if ($this->dialogHelper) {
            return $this->dialogHelper;
        }

        return $this->dialogHelper = new DialogHelper();
    }

    /**
     * Set Dialog Helper
     *
     * @param DialogHelper $dialogHelper
     * @return Migration
     */
    public function setDialogHelper(DialogHelper $dialogHelper)
    {
        $this->dialogHelper = $dialogHelper;
        return $this;
    }
}



