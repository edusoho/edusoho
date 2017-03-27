<?php

namespace Phpmig\Api;

use Phpmig\Api\PhpmigApplication;
use Symfony\Component\Console\Output;

/**
 * @group unit
 * @coversDefaultClass Phpmig\Api\PhpmigApplication
 *
 * @author      Cody Phillips
 */
class PhpmigApplicationTest extends \PHPUnit_Framework_TestCase
{
    private $prev_version = '20141104210000';
    private $current_version = '20141104220000';
    private $next_version = '20141104230000';
    private $output;
    private $temp_dir;
    
    public function setup()
    {
        $this->output = new Output\NullOutput();
        $this->setTempDir($this->makeTempDir());
    }
    
    public function tearDown()
    {
        $this->cleanTempDir($this->getTempDir());
    }
    
    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        $app = new PhpmigApplication(
            $this->getContainer(
                $this->getAdapter(),
                array($this->getTempDir() . DIRECTORY_SEPARATOR . "InvalidMigration.php"),
                $this->getTempDir()
            ),
            $this->output
        );
        $this->assertInstanceOf("Phpmig\Api\PhpmigApplication", $app);
    }
    
    /**
     * @covers ::up
     */
    public function testUp()
    {
        $adapter = $this->getAdapter(array($this->prev_version, $this->current_version));
        $migrations = $this->getMigrations();
        $this->createTestMigrations($migrations);
        
        $container = $this->getContainer($adapter, $migrations, $this->getTempDir());
        $container['phpmig.migrator'] = $this->getMigrator($adapter, $container, $this->output, 1, 0);
        
        $app = new PhpmigApplication($container, $this->output);
        
        $app->up($this->next_version);
    }
    
    /**
     * @covers ::down
     */
    public function testDown()
    {
        $adapter = $this->getAdapter(array($this->prev_version, $this->current_version));
        $migrations = $this->getMigrations();
        $this->createTestMigrations($migrations);
        
        $container = $this->getContainer($adapter, $migrations, $this->getTempDir());
        $container['phpmig.migrator'] = $this->getMigrator($adapter, $container, $this->output, 0, 1);
        
        $app = new PhpmigApplication($container, $this->output);
        
        $app->down($this->prev_version);
    }
    
    /**
     * @covers ::getMigrations
     * @covers ::loadMigrations
     * @covers ::migrationToClassName
     */
    public function testGetMigrations()
    {
        $migrations = $this->getMigrations();
        $this->createTestMigrations($migrations);
        
        $app = new PhpmigApplication(
            $this->getContainer(
                $this->getAdapter(array($this->current_version)),
                $migrations,
                $this->getTempDir()
            ),
            $this->output
        );
        
        // up
        $this->assertCount(3, $app->getMigrations(0, $this->next_version));
        $this->assertCount(3, $app->getMigrations(0, null));
        $this->assertCount(2, $app->getMigrations($this->prev_version, $this->next_version));
        $this->assertCount(1, $app->getMigrations($this->current_version, $this->next_version));
        $this->assertCount(0, $app->getMigrations($this->next_version, $this->next_version));
        
        // down
        $this->assertCount(1, $app->getMigrations($this->next_version, $this->current_version));
        $this->assertCount(1, $app->getMigrations($this->current_version, $this->prev_version));
        $this->assertCount(2, $app->getMigrations($this->next_version, $this->prev_version));
        $this->assertCount(3, $app->getMigrations($this->next_version, 0));
    }
    
    /**
     * @covers ::getMigrations
     * @covers ::loadMigrations
     * @covers ::migrationToClassName
     * @expectedException \InvalidArgumentException
     * @dataProvider getMigrationsExceptionProvider
     */
    public function testGetMigrationsException($migrations, $class_names, $extends)
    {
        foreach ($migrations as &$migration) {
            $migration = $this->getTempDir() . DIRECTORY_SEPARATOR . $migration;
        }

        $this->createTestMigrations($migrations, $class_names, $extends);
        $adapter = $this->getAdapter();
        $container = $this->getContainer($adapter, $migrations, $this->getTempDir());
        
        $app = new PhpmigApplication($container, $this->output);
        $app->getMigrations(0);
    }
    
    /**
     * @return array
     */
    public function getMigrationsExceptionProvider()
    {
        return array(
            // Duplicate version
            array(
                array("20141112000000_Test01.php", "20141112000000_Test02.php"),
                array("Test01", "Test02"),
                "Migration"
            ),            
            // Duplicate name
            array(
                array("20141112030000_Test03.php", "20141112040000_Test03.php"),
                array("Test03", "Test04"),
                "Migration"
            ),
            // Class not found
            array(
                array("20141112050000_Test05.php"),
                array("InvalidClass"),
                "Migration"
            ),
            // Bad inheritance
            array(
                array("20141112060000_Test06.php"),
                array("Test06"),
                "\Exception"
            )
        );
    }
    
    /**
     * @covers ::getVersion
     */
    public function testGetVersion()
    {
        $migrations = $this->getMigrations();
        $this->createTestMigrations($migrations);
        
        $app = new PhpmigApplication(
            $this->getContainer(
                $this->getAdapter(array($this->current_version)),
                $migrations,
                $this->getTempDir()
            ),
            $this->output
        );
        
        $this->assertEquals($this->current_version, $app->getVersion());
        
        $app = new PhpmigApplication(
            $this->getContainer(
                $this->getAdapter(),
                array(),
                $this->getTempDir()
            ),
            $this->output
        );
        
        $this->assertEquals(0, $app->getVersion());
    }
    
    /**
     * @param array $version
     * @return Phpmig\Adapter\AdapterInterface mock
     */
    protected function getAdapter(array $versions = array())
    {
        $adapter = $this->getMock('Phpmig\Adapter\AdapterInterface');
        $adapter->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue($versions));
        return $adapter;
    }
    
    /**
     * @param object $adapter
     * @param object $container
     * @param object $output
     * @param int $times_up
     * @param int $times_down
     * @return Phpmig\Migration\Migrator mock
     */
    protected function getMigrator($adapter, $container, $output, $times_up, $times_down)
    {
        $migrator = $this->getMock("Phpmig\Migration\Migrator", array("up", "down"), array($adapter, $container, $output));
        if ($times_up > 0) {
            $migrator->expects($this->exactly($times_up))
                ->method("up")
                ->with($this->isInstanceOf("Phpmig\Migration\Migration"));
        }
        
        if ($times_down > 0) {
            $migrator->expects($this->exactly($times_down))
                ->method("down")
                ->with($this->isInstanceOf("Phpmig\Migration\Migration"));
        }
        return $migrator;
    }
    
    /**
     * @param object $adapter
     * @param array $migrations
     * @param string $migrations_path
     */
    protected function getContainer($adapter, array $migrations, $migrations_path)
    {
        return new \ArrayObject(array(
            'phpmig.adapter' => $adapter,
            'phpmig.migrations' => $migrations,
            'phpmig.migrations_path' => $migrations_path
        ));
    }
    
    /**
     * @return array Migration filenames
     */
    protected function getMigrations()
    {
        $tmp_dir = $this->getTempDir() . DIRECTORY_SEPARATOR;
        $seed = md5(mt_rand());
        return array(
            $tmp_dir . $this->prev_version . "_Test" . substr($seed, 0, 8) . ".php",
            $tmp_dir . $this->current_version . "_Test" . substr($seed, 8, 8) . ".php",
            $tmp_dir . $this->next_version . "_Test" . substr($seed, 16, 8) . ".php",
            $tmp_dir . "InvalidTest" . substr($seed, 24, 8) . ".php"
        );
    }
    
    /**
     * @return string
     */
    protected function getTempDir()
    {
       return $this->temp_dir; 
    }
    
    /**
     * @param string $dir
     */
    protected function setTempDir($dir)
    {
        $this->temp_dir = $dir;
    }
    
    /**
     * @return string The temp directory created
     */
    protected function makeTempDir()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(mt_rand());
        mkdir($dir);
        
        return $dir;
    }
    
    protected function cleanTempDir()
    {
        $dir = $this->getTempDir();
        $dh = opendir($dir);
        if ($dh !== false) {
            while (($file = readdir($dh))) {
                if ($file[0] == ".") {
                    continue;
                }
                
                if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                    $this->cleanTempDir($dir . DIRECTORY_SEPARATOR . $file);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dh);
        rmdir($dir);
    }
    
    protected function createTestMigrations(array $migrations, array $class_names = null, $extends = "Migration")
    {
        $class =<<< 'CODE'
<?php

use Phpmig\Migration\Migration;

class %s extends %s
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer(); 
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer(); 
    }
}
CODE;
        foreach ($migrations as $i => $file) {
            if ($class_names !== null && isset($class_names[$i])) {
                $class_name = $class_names[$i];
            } else {
                $class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', basename($file, ".php")))));
            }
            file_put_contents($file, sprintf($class, $class_name, $extends));
        }
    }
}
