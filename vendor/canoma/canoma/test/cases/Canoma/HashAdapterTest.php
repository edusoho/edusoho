<?php
/**
 * This test-script tests all shared adapter functiontionality
 *
 * @author Mark van der Velden <mark@dynom.nl>
 */ 
class HashAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $adapterNames = array(
        'Adler32',
        'Crc32',
        'Md5',
        'Salsa20',
    );


    /**
     * @dataProvider adapterNameProvider
     */
    public function testAllAvailableAdapters($adapterName)
    {
        $adapterNameLC = strtolower($adapterName);

        // Test if we have the required algorithm, if not, skip the tests.
        if ( ! in_array($adapterNameLC, hash_algos())) {
            $this->markTestSkipped('Skipping, because "'. $adapterNameLC .'" is not supported on this platform.');
        }

        $className = '\Canoma\HashAdapter\\'. $adapterName;
        $this->assertTrue(class_exists($className));
    }


    /**
     * Provides adapter names
     *
     * @return array
     */
    public function adapterNameProvider()
    {
        $adapters = array();
        foreach ($this->adapterNames as $name) {
            $adapters[] = array($name);
        }

        return $adapters;
    }


    /**
     * Provides adapter objects
     *
     * @return array
     */
    public function adapterProvider()
    {
        $adapters = array();
        foreach ($this->adapterNames as $name) {
            $className = '\Canoma\HashAdapter\\'. $name;
            $adapters[] = array( new $className );
        }

        return $adapters;
    }


    /**
     * Testing if all adapters correctly favor right, when left is lower.
     *
     * @dataProvider adapterProvider
     *
     * @param \Canoma\HashAdapterInterface $adapter
     */
    public function testAllForFavorRightCompare(\Canoma\HashAdapterInterface $adapter)
    {
        $this->assertEquals(-1, $adapter->compare(12, 100), 'Expecting 12 to be less then 100 and a result of -1');
    }


    /**
     * Testing if all adapters correctly favor right, when left is lower.
     *
     * @dataProvider adapterProvider
     *
     * @param \Canoma\HashAdapterInterface $adapter
     */
    public function testAllForFavorLeftCompare(\Canoma\HashAdapterInterface $adapter)
    {
        $this->assertEquals(1, $adapter->compare(100, 12), 'Expecting 100 to be greater then 12 and a result of 1');
    }


    /**
     * Testing if all adapters correctly favor right, when left is lower.
     *
     * @dataProvider adapterProvider
     *
     * @param \Canoma\HashAdapterInterface $adapter
     */
    public function testAllForFavorEqualCompare(\Canoma\HashAdapterInterface $adapter)
    {
        $this->assertEquals(0, $adapter->compare(100, 100), 'Expecting 100 to be equal then 100 and a result of 0');
    }
}