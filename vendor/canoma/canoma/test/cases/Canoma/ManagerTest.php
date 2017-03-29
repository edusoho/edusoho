<?php
/**
 * @author Mark van der Velden <mvdvelden@ibuildings.nl>
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Canoma\Manager
     */
    private $manager;

    public function setUp()
    {
        $this->manager = new \Canoma\Manager(
            new \Canoma\HashAdapter\Md5(),
            42
        );
    }


    public function testAddNode()
    {
        $this->assertEquals(0, count($this->manager->getAllNodes()), 'Expecting exactly 0 nodes since none were added.');

        $this->manager->addNode('foo');
        $this->assertEquals(1, count($this->manager->getAllNodes()), 'Expecting exactly 1 node after adding 1');
    }


    /**
     * Adding duplicate nodes should fail.
     *
     * @expectedException \RuntimeException
     */
    public function testAddNodeDuplicateFail()
    {
        $this->manager->addNode('foo');
        $this->assertEquals(1, count($this->manager->getAllNodes()));

        // This should throw an exception
        $this->manager->addNode('foo');
        $this->fail('Expecting exactly 1 node after adding 1');
    }


    /**
     * Adding invalid node names should fail.
     *
     * @expectedException \RuntimeException
     */
    public function testAddNodeInvalidNameFail()
    {
        // This should throw an exception
        $this->manager->addNode(2);
        $this->fail('Expecting exactly 1 node after adding 1');
    }


    /**
     * Expecting the replica count to determine the amount of positions that are generated
     */
    public function testNodePositionsForSingleNode()
    {
        $manager = new \Canoma\Manager(
            new \Canoma\HashAdapter\Md5(),
            42
        );

        $manager->addNode('foo');
        $this->assertEquals(42, count($manager->getPositionsOfNode('foo')), 'Expecting 42 positions');
    }


    /**
     * Expecting the replica count to determine the amount of positions that are generated
     * @expectedException \RuntimeException
     */
    public function testNodePositionsForInvalidNode()
    {
        // This should throw an exception
        $this->manager->getPositionsOfNode('f-o-o b-a-r');
        $this->fail('Expecting an exception to be thrown when positions of an unexisting node is requested.');
    }


    /**
     * Expecting each position to raise the total amount of positions, and without node collisions
     */
    public function testNodePositions()
    {
        $manager = new \Canoma\Manager(
            new \Canoma\HashAdapter\Md5(),
            12
        );

        $manager->addNode('foo');
        $this->assertEquals(12, count($manager->getAllPositions()), 'Expecting 12 positions');

        $manager->addNode('1foo');
        $this->assertEquals(24, count($manager->getAllPositions()), 'Expecting 24 positions');

        $manager->addNode('foo1');
        $this->assertEquals(36, count($manager->getAllPositions()), 'Expecting 36 positions');
    }


    public function testGetNodeForString()
    {
        $this->manager
                ->addNode('A')
                ->addNode('B')
                ->addNode('C')
                ->addNode('D');

        $cacheIdentifier = 'user:42';

        $node = $this->manager->getNodeForString($cacheIdentifier);
        $this->assertInternalType('string', $node, 'Expecting the node to be a string');
    }


    /**
     *
     */
    public function testGetMultipleNodesForString()
    {
        $manager = new \Canoma\Manager(
            new \Canoma\HashAdapter\Md5(),
            5
        );
        $manager->addNode('A');
        $manager->addNode('B');
        $manager->addNode('C');
        $manager->addNode('D');

        $nodes = $manager->getMultipleNodesForString('user:42', 2);
        $this->assertEquals(
            array('A', 'C'),
            array_values($nodes),
            'Expecting the nodes to be ..'
        );
    }


    /**
     * @dataProvider multipleNodesForStringProvider
     *
     * @param int $nodeCount
     * @param int $nodeAmount
     * @param array $matchingNodes
     *
     * @return void
     */
    public function testGetMultipleNodesForStringDynamic($nodeCount, $nodeAmount, array $matchingNodes)
    {
        $manager = new \Canoma\Manager(
            new \Canoma\HashAdapter\Md5(),
            5
        );

        for ($i = 0; $i < $nodeCount; $i++) {
            $manager->addNode('A '. $i);
        }

        $nodes = $manager->getMultipleNodesForString('user:42', $nodeAmount);
        $this->assertEquals($matchingNodes, $nodes);
    }


    /**
     * Return a set of arguments for testGetMultipleNodesForStringDynamic, arguments order:
     * - Amount of nodes to create (nodeCount)
     * - Amount of nodes to return (nodeAmount)
     * - Array with nodes
     *
     * @return array
     */
    public function multipleNodesForStringProvider()
    {
        return array(
            array(200, 2, array('A 182' => 'A 182', 'A 32' => 'A 32')),
            array(50, 2, array('A 32' => 'A 32', 'A 15' => 'A 15')),
            array(10, 2, array('A 0' => 'A 0', 'A 8' => 'A 8')),
            array(5, 5, array('A 0' => 'A 0', 'A 1' => 'A 1', 'A 2' => 'A 2', 'A 3' => 'A 3', 'A 4' => 'A 4')),
            array(5, 3, array('A 0' => 'A 0', 'A 3' => 'A 3', 'A 1' => 'A 1')),
        );
    }


    /**
     * Testing the wrapping of fetching nodes
     */
    public function testMultipleNodesForStringAddRemaining()
    {
        $adapter = Phake::mock('\Canoma\HashAdapter\Md5');
        Phake::when($adapter)->compare(Phake::anyParameters())->thenCallParent();

        $manager = new \Canoma\Manager(
            $adapter,
            1
        );

        Phake::when($adapter)->hash(Phake::anyParameters())->thenReturn(10);
        $manager->addNode('a');

        Phake::when($adapter)->hash(Phake::anyParameters())->thenReturn(20);
        $manager->addNode('b');

        Phake::when($adapter)->hash(Phake::anyParameters())->thenReturn(30);
        $manager->addNode('c');

        Phake::when($adapter)->hash(Phake::anyParameters())->thenReturn(40);
        $manager->addNode('d');

        Phake::when($adapter)->hash(Phake::anyParameters())->thenReturn(35);
        $this->assertEquals('d', $manager->getNodeForString('this should get position 35, and thus node D'));

        $this->assertEquals(
            array('d', 'a'),
            array_values($manager->getMultipleNodesForString('a')),
            'This should get nodes D and A, since the position is 35.'
        );
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testGetMultipleNodesForStringTooLargeAmount()
    {
        $this->manager
                ->addNode('A')
                ->addNode('B');

        $this->manager->getMultipleNodesForString('foo', 6);
        $this->fail('Expecting an exception being thrown, since we request more nodes then have been defined.');
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testGetMultipleNodesWithAInsaneAmount()
    {
        $this->manager->addNode('B');

        $this->manager->getMultipleNodesForString('foo', 0);
        $this->fail('Expecting an exception being thrown, since we requested a <1 amount of nodes.');
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testGetMultipleNodesWithInvalidAmount()
    {
        $this->manager->addNode('B');

        $this->manager->getMultipleNodesForString('foo', true);
        $this->fail('Expecting an exception being thrown, since $amount is of an invalid type.');
    }


    /**
     * @dataProvider deviationParameterProvider
     *
     * @param int $replicates
     * @param int $nodes
     * @param int $keyCount
     * @param int $expectedSD
     */
    public function testCorrectDeviations($replicates = 37, $nodes = 2, $keyCount = 100, $expectedSD = 4)
    {
        $manager = new \Canoma\Manager(
            new \Canoma\HashAdapter\Md5(),
            $replicates
        );

        // Adding the amount of nodes
        for ($i = 0; $i < $nodes; $i++) {
            $manager->addNode('Node '. $i);
        }

        // Do lookups for the amount of cache-keys
        $result = array();
        for ($i = 0; $i < $keyCount; $i++) {
            $result[] = $manager->getNodeForString("user:". $i);
        }


        $standardDeviation = $this->calculateSDFromResult($result);
        $this->assertEquals($expectedSD, (int) $standardDeviation, 'Expecting the standard deviation for these parameters to be '. $expectedSD);
    }

    /**
     * @return array replicates, nodes, keyCount, expectedSD
     */
    public function deviationParameterProvider()
    {
        return array(
            array(37, 2, 100, 3),
            array(10, 10, 100, 4),
            array(20, 3, 1000, 18),
        );
    }


    /**
     * Helper method, calculating the standard deviation of a list of nodes
     *
     * @param array $result
     * @return float
     */
    private function calculateSDFromResult(array $result)
    {
        $resultSummary = array_count_values($result);
        $mean = array_sum($resultSummary) / count($resultSummary);

        $deviationResult = array();
        foreach ($resultSummary as $nodeCount) {
            $deviationResult[] = pow($nodeCount - $mean, 2);
        }

        return sqrt(array_sum($deviationResult) / count($deviationResult));
    }


    /**
     * Testing 100 nodes with 500 replica's
     *
     * @group performanceTest
     */
    public function testManyNodePositions()
    {
        $manager = new \Canoma\Manager(
            new \Canoma\HashAdapter\Crc32(),
            500
        );

        for ($i = 0; $i < 100; $i++) {
            $manager->addNode('10.2.2.'. $i .':1142');
        }

        $this->assertEquals(500*100, count($manager->getAllPositions()), 'Expecting 50.000 positions.');
    }


    /**
     * Testing the 'getAdapter' functionality
     */
    public function testGetAdapter()
    {
        $this->assertInstanceOf('\Canoma\HashAdapterInterface', $this->manager->getAdapter());
    }


    public function testAddNodes()
    {
        $this->manager->addNodes(
            array(
                 'a',
                 'b'
            )
        );

        $this->assertCount(2, $this->manager->getAllNodes(), 'Expecting two nodes, after adding two.');

        $this->manager
                ->addNodes(array('c'))
                ->addNodes(array('d'));

        $this->assertCount(4, $this->manager->getAllNodes(), 'Expecting four nodes, after adding two more.');
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testAddNodesDuplicate()
    {
        $this->manager->addNodes(
            array(
                 'a',
                 'b'
            )
        );

        $this->assertCount(2, $this->manager->getAllNodes(), 'Expecting two nodes, after adding two.');

        $this->manager->addNodes(array('a'));

        $this->fail('Expecting an exception being thrown after adding a duplicate node.');
    }


    /**
     * Testing that we check for the existence of a node
     */
    public function testHasNode()
    {
        $this->manager->addNodes(
            array(
                 'a',
            )
        );

        $this->assertFalse($this->manager->hasNode('A'), 'Expecting node "A" to not be defined.');
        $this->assertTrue($this->manager->hasNode('a'), 'Expecting node "a" is defined.');
    }


    /**
     * Testing the removal of a node
     */
    public function testRemoveNode()
    {
        $manager = new \Canoma\Manager(new \Canoma\HashAdapter\Md5(), 42);
        $manager->addNodes(
            array(
                 'a',
                 'b',
                 'c',
                 'd'
            )
        );

        // Confirming conditions before removing the node
        $this->assertCount(4, $manager->getAllNodes(), 'Expecting four nodes, after adding four.');
        $this->assertCount(168, $manager->getAllPositions(), 'Expecting 168 positions, 42 * 4 nodes.');

        $manager->removeNode('a');

        // Confirming that after removing the node, we left in a sane state.
        $this->assertCount(3, $manager->getAllNodes(), 'Expecting three nodes, after removing one.');
        $this->assertCount(126, $manager->getAllPositions(), 'Expecting 126 positions, 42 * 3 nodes.');

        // Confirming that removing keys doesn't influence the order
        $sortedPositions = $manager->getAllPositions();
        ksort($sortedPositions, SORT_REGULAR);

        $this->assertSame(
            $sortedPositions,
            $manager->getAllPositions(),
            'Expecting that sorting has no influence on the positions'
        );
    }
}
