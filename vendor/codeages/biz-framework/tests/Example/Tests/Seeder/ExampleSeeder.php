<?php

namespace Tests\Example\Tests\Seeder;

use Codeages\Biz\Framework\Testing\DatabaseSeeder;

class ExampleSeeder extends DatabaseSeeder
{
    public function run($isRun = true)
    {
        $rows = array(
            array(
                'id' => 1,
                'name' => 'test_name_1',
                'code' => 'test_code_1',
                'counter1' => 1,
                'counter2' => 2,
                'ids1' => '|1|2|3|',
                'ids2' => '',
                'null_value' => null,
                'content' => 'test content 1',
                'php_serialize_value' => serialize(array(1, 2, 3)),
                'json_serialize_value' => json_encode(array(1, 2, 3)),
                'delimiter_serialize_value' => '|1|2|3|',
                'created_time' => time(),
                'updated_time' => time(),
            ),
            array(
                'id' => 2,
                'name' => 'test_name_2',
                'code' => 'test_code_2',
                'counter1' => 1,
                'counter2' => 2,
                'ids1' => '|1|2|3|',
                'ids2' => '',
                'null_value' => null,
                'content' => 'test content 1',
                'php_serialize_value' => serialize(array(1, 2, 3)),
                'json_serialize_value' => json_encode(array(1, 2, 3)),
                'delimiter_serialize_value' => '|1|2|3|',
                'created_time' => time(),
                'updated_time' => time(),
            ),
            array(
                'id' => 3,
                'name' => 'test_name_3',
                'code' => 'test_code_3',
                'counter1' => 1,
                'counter2' => 2,
                'ids1' => '|1|2|3|',
                'ids2' => '',
                'null_value' => null,
                'content' => 'test content 1',
                'php_serialize_value' => serialize(array(1, 2, 3)),
                'json_serialize_value' => json_encode(array(1, 2, 3)),
                'delimiter_serialize_value' => '|1|2|3|',
                'created_time' => time(),
                'updated_time' => time(),
            ),
        );

        return $this->insertRows('example', $rows, $isRun);
    }
}
