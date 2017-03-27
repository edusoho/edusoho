<?php
namespace Codeages\Biz\Framework\Tests\DataStructure;

use Codeages\Biz\Framework\DataStructure\UniquePriorityQueue;

class UniquePriorityQueueTest extends \PHPUnit_Framework_TestCase
{

    public function testInsert()
    {
        $queue = new UniquePriorityQueue();
        $queue->insert('test 5', 5);
        $queue->insert('test 5.1', 5);
        $queue->insert('test 5.2', 5);
        $queue->insert('test 5.3', 5);
        $queue->insert('test 8', 8);
        $queue->insert('test 3', 3);
        $queue->insert('test 9', 9);

        $queue->insert('test 8', 8);
        $queue->insert('test 3', 3);

        $this->assertEquals(7, count($queue));

        $items = array();
        foreach ($queue as $item) {
            $items[] = $item;
        }

        $this->assertEquals('test 9', $items[0]);
        $this->assertEquals('test 8', $items[1]);
        $this->assertEquals('test 5', $items[2]);
        $this->assertEquals('test 5.1', $items[3]);
        $this->assertEquals('test 5.2', $items[4]);
        $this->assertEquals('test 5.3', $items[5]);
        $this->assertEquals('test 3', $items[6]);
    }

}