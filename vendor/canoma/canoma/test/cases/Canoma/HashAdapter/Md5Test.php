<?php
/**
 * @author Mark van der Velden <mark@dynom.nl>
 */
class Md5Test extends \PHPUnit_Framework_TestCase
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
        $this->adapterConfig[\Canoma\Factory::CONF_HASHING_ADAPTER] = 'Md5';

        // Test if we have the required algorithm, if not, skip the tests.
        if ( ! in_array('md5', hash_algos())) {
            $this->markTestSkipped('Skipping, because md5 is not supported on this platform.');
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
                '054d87c680f6629003e1c43469a537a7'
            ),
        );
    }


    /**
     * Emulate the scenario where we are on a 32 bit OS.
     */
    public function test32BitOS()
    {
        $adapter = Phake::mock('\Canoma\HashAdapter\Md5');

        Phake::when($adapter)->compare(Phake::anyParameters())->thenCallParent();
        Phake::when($adapter)->hash(Phake::anyParameters())->thenCallParent();
        Phake::when($adapter)->is32bitOS()->thenReturn(true);

        $this->assertEquals(-1, $adapter->compare(1, 2));
    }
}