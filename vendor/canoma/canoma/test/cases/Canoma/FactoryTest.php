<?php

use \Canoma\Factory;

/**
 * @author Mark van der Velden <mark@dynom.nl>
 */ 
class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testMinimalConstructionOfManager()
    {
        $config = array(
            Factory::CONF_REPLICA_COUNT => 200,
            Factory::CONF_HASHING_ADAPTER => 'Md5'
        );

        $factory = new Factory();
        $manager = $factory->createManager($config);

        $this->assertTrue($manager instanceof \Canoma\Manager);
    }


    /**
     * @dataProvider adapterNameProvider
     */
    public function testMinimalConstructionOfAdapter($adapterName)
    {
        // This should be the name.
        $fqn = '\Canoma\HashAdapter\\'. $adapterName;

        // Specifying the adapter name in the configuration
        $config = array(
            Factory::CONF_HASHING_ADAPTER => $adapterName
        );

        // Create the adapter, based on the configuration
        $factory = new Factory();
        $adapter = $factory->createAdapter($config);

        $this->assertInstanceOf($fqn, $adapter, 'Expecting the adapter to be an instance of "'. $fqn .'"');
        $this->assertInstanceOf('\Canoma\HashAdapterInterface', $adapter, 'Expecting the adapter to implement the hash adapter interface');
    }


    public function testConstructionOfManagerWithNodes()
    {
        $config = array(
            Factory::CONF_REPLICA_COUNT => 200,
            Factory::CONF_HASHING_ADAPTER => 'Md5',
            Factory::CONF_NODES => array(
                'cache-1.example.com:11211',
                'cache-2.example.com:11211',
                'cache-3.example.com:11211',
            )
        );

        $factory = new Factory();
        $manager = $factory->createManager($config);
        $this->assertCount(3, $manager->getAllNodes(), 'Expecting 3 nodes to be added.');
    }


    /**
     * Provides adapter names.
     *
     * @return array
     */
    public function adapterNameProvider()
    {
        return array(
            array('Md5'),
            array('Crc32'),
            array('Adler32'),
            array('Salsa20'),
        );
    }


    /**
     * @dataProvider invalidManagerConfigurationProvider
     * @expectedException \InvalidArgumentException
     * @param array $config
     */
    public function testCreateManagerInvalidConfiguration(array $config)
    {
        $factory = new Factory();

        // This should thrown an exception
        $factory->createManager(
            $config
        );

        $this->fail('Expected exceptions on invalid manager configuration.');
    }


    /**
     * @return array Invalid replica count values
     */
    public function invalidManagerConfigurationProvider()
    {
        return array(
            array(array()),
            array(array(Factory::CONF_REPLICA_COUNT => 'foo')),
            array(array(Factory::CONF_REPLICA_COUNT => -1)),
        );
    }


    /**
     * @dataProvider invalidAdapterConfigurationProvider
     * @expectedException \InvalidArgumentException
     * @param array $config
     */
    public function testCreateAdapterInvalidConfiguration(array $config)
    {
        $factory = new Factory();

        // This should thrown an exception
        $factory->createAdapter(
            $config
        );

        $this->fail('Expected exceptions on invalid adapter configuration.');
    }


    /**
     * @return array Invalid replica count values
     */
    public function invalidAdapterConfigurationProvider()
    {
        return array(
            array(array()), // Undefined key
        );
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testCreateAdapterInvalidConfigurationClass()
    {
        $factory = new Factory();

        // This should thrown an exception
        $factory->createAdapter(
            array(Factory::CONF_HASHING_ADAPTER => 'someUnexistingClassIsBeingPassedAlongHere')
        );

        $this->fail('Expected exceptions on invalid adapter definition, the class does not exist.');
    }
}