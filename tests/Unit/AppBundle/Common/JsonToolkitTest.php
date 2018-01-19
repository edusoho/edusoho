<?php

namespace AppBundle\Common\Tests;

use AppBundle\Common\JsonToolkit;
use Biz\BaseTestCase;

class JsonToolkitTest extends BaseTestCase
{
    public function testPrettyPrint()
    {
        $array = array(
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => array(
                'key3-1' => 'value3-1'
            ),
        );
        $result = JsonToolkit::prettyPrint(json_encode($array));
        $expected = "{\n\t\"key1\": \"value1\",\n\t\"key2\": \"value2\",\n\t\"key3\": {\n\t\t\"key3-1\": \"value3-1\"\n\t}\n}";
        $this->assertEquals($expected, $result);
    }
}