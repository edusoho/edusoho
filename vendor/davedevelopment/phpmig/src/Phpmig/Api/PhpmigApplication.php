<?php
/**
 * @package    Phpmig
 * @subpackage Api
 */
namespace Phpmig\Api;

use Phpmig\Migration;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The phpmig application for API processing
 *
 * Usage:
 * <code>
 * $container = include_once "/full/path/to/phpmig.php";
 * $output = new Symfony\Component\Console\Output\OutputInterface\BufferedOutput;
 * $phpmig = new Phpmig\Api\PhpmigApplication($container, $output);
 * $phpmig->up(); // upgrade to latest version
 * echo $output->output(); // fetch output
 * </code>
 *
 * @author      Cody Phillips
 */
class PhpmigApplication
{
    protected $container;
    protected $output;
    protected $migrations;
    
    public function __construct(\ArrayAccess $container, OutputInterface $output)
    {
        $this->container = $container;
        $this->output = $output;
        if (!isset($this->container['phpmig.migrator']))
            $this->container['phpmig.migrator'] = new Migration\Migrator($container['phpmig.adapter'], $this->container, $this->output);
        
        $migrations = array();
        if (isset($this->container['phpmig.migrations'])) {
            $migrations = $this->container['phpmig.migrations'];
            foreach ($migrations as &$migration) {
                $migration = realpath($migration);
            }
            unset($migration);
        }
        if (isset($this->container['phpmig.migrations_path'])) {
            $migrationsPath = realpath($this->container['phpmig.migrations_path']);
            $migrations = array_merge($migrations, glob($migrationsPath . DIRECTORY_SEPARATOR . '*.php'));
        }
        
        $this->migrations = array_unique($migrations);
    }
    
    /**
     * Migrate up
     *
     * @param string $version The version to migrate up to
     */
    public function up($version = null)
    {
        $adapter = ! empty($this->container['phpmig.adapter']) ? $this->container['phpmig.adapter'] : null;

        if ($adapter == null) {

            throw new RuntimeException("The container must contain a phpmig.adapter key!");
        }

        if (!$adapter->hasSchema()) {

            $this->container['phpmig.adapter']->createSchema();
        }

        foreach ($this->getMigrations($this->getVersion(), $version) as $migration) {
            $this->container['phpmig.migrator']->up($migration);
        }
    }
    
    /**
     * Migrate down
     *
     * @param string $version The version to migrate down to
     */
    public function down($version = 0)
    {
        if ($version === null || $version < 0)
            throw new \InvalidArgumentException("Invalid version given, expected  >= 0.");
            
        foreach ($this->getMigrations($this->getVersion(), $version) as $migration) {
            $this->container['phpmig.migrator']->down($migration);
        }
    }
    
    /**
     * Load all migrations to get $from to $to
     *
     * @param string $from The from version
     * @param string $to The to version
     * @return array An array of Phpmig\Migration\Migration objects to process
     */
    public function getMigrations($from, $to = null)
    {
        $migrations = array();
        
        if ($to > $from || $to === null) {
            ksort($this->migrations);
        } else {
            krsort($this->migrations);
        }
        
        foreach ($this->migrations as $path) {
            preg_match('/^[0-9]+/', basename($path), $matches);
            if (!array_key_exists(0, $matches)) {
                continue;
            }
            
            $version = $matches[0];
    
            // up
            if ($to > $from || $to === null) {
                if ($version > $from && ($version <= $to || $to === null)) {
                    $migrations[] = $path;
                }
            // down
            } elseif ($to < $from && $version > $to && $version <= $from) {
                $migrations[] = $path;
            }
        }
        
        return $this->loadMigrations($migrations);
    }
    
    /**
     * Loads migrations from the given set of available migration files
     *
     * @param array $migrations An array of migration files to prepare migrations for
     * @return array An array of Phpmig\Migration\Migration objects
     */
    protected function loadMigrations($migrations)
    {
        $versions = array();
        $names = array();
        foreach ($migrations as $path) {
            if (!preg_match('/^[0-9]+/', basename($path), $matches)) {
                throw new \InvalidArgumentException(sprintf('The file "%s" does not have a valid migration filename', $path));
            }
    
            $version = $matches[0];
    
            if (isset($versions[$version])) {
                throw new \InvalidArgumentException(sprintf('Duplicate migration, "%s" has the same version as "%s"', $path, $versions[$version]->getName()));
            }
    
            $migrationName = preg_replace('/^[0-9]+_/', '', basename($path));
            if (false !== strpos($migrationName, '.')) {
                $migrationName = substr($migrationName, 0, strpos($migrationName, '.'));
            }
            $class = $this->migrationToClassName($migrationName);
    
            if (isset($names[$class])) {
                throw new \InvalidArgumentException(sprintf(
                    'Migration "%s" has the same name as "%s"',
                    $path,
                    $names[$class]
                ));
            }
            $names[$class] = $path;
    
            require_once $path;
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not find class "%s" in file "%s"',
                    $class,
                    $path
                ));
            }
    
            $migration = new $class($version);
    
            if (!($migration instanceof Migration\Migration)) {
                throw new \InvalidArgumentException(sprintf(
                    'The class "%s" in file "%s" must extend \Phpmig\Migration\Migration',
                    $class,
                    $path
                ));
            }
    
            $migration->setOutput($this->output); // inject output
    
            $versions[$version] = $migration;
        }
    
        return $versions;
    }
    
    /**
     * Transform create_table_user to CreateTableUser
     *
     * @param string $migrationName The migration name
     * @return string The CamelCase migration name
     */
    protected function migrationToClassName($migrationName)
    {
        $class = str_replace('_', ' ', $migrationName);
        $class = ucwords($class);
        return str_replace(' ', '', $class);
    }
    
    /**
     * Returns the current version
     *
     * @return string The current installed version
     */
    public function getVersion()
    {
        $versions = $this->container['phpmig.adapter']->fetchAll();
        sort($versions);
    
        if (!empty($versions)) {
            return end($versions);
        }
        return 0;
    }
}
