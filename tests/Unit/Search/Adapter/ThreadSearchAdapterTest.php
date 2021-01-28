<?php

namespace Tests\Unit\Search\Adapter;

use Biz\BaseTestCase;
use Biz\Search\Adapter\SearchAdapterFactory;

class ThreadSearchAdapterTest extends BaseTestCase
{
    public function testAdapt()
    {
        $result = SearchAdapterFactory::create('thread')->adapt(array(
            array(
                'threadId' => 2,
            ),
            array(
                'threadId' => 3,
            ),
        ));
        $this->assertArrayEquals(
            array(
                array(
                    'threadId' => 2,
                    'id' => 2,
                ),
                array(
                    'threadId' => 3,
                    'id' => 3,
                ),
            ),
            $result
        );
    }
}
