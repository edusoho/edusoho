<?php
/**
 * @author Mark van der Velden <mark@dynom.nl>
 */
class Salsa20Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Canoma\Factory
     */
    private $factory;
    private $adapterConfig = array();


    public function setUp()
    {
        // Use the factory and define the required config.
        $this->factory = new \Canoma\Factory();
        $this->adapterConfig[\Canoma\Factory::CONF_HASHING_ADAPTER] = 'Salsa20';

        // Test if we have the required algorithm, if not, skip the tests.
        if ( ! in_array('salsa20', hash_algos())) {
            $this->markTestSkipped('Skipping, because salsa20 is not supported on this platform.');
        }
    }

    /**
     * @dataProvider simpleStringProvider
     */
    public function testSimpleHashing($someString, $expectedHash)
    {
        $adapter = $this->factory->createAdapter($this->adapterConfig);

        $this->assertTrue(ctype_alnum($adapter->hash($someString)));
        $this->assertEquals($expectedHash, $adapter->hash($someString));
    }


    /**
     * Provider of strings.
     *
     * Syntax:
     *  array(<challenge>, <hashed representation>)
     *
     * @return array
     */
    public function simpleStringProvider()
    {
        return array(
            array(
                'A simple string, that should definitely not pass a ctype_alnum test!',
                '7d7e6a85cf3e4840aff175a38d4705318a9c0ac74848d2499155793fb4c3de952c324a79f41e805065b714494de2b2255d2a482a2c6ad9a75f06e47cddf92b44'
            ),
        );
    }
}