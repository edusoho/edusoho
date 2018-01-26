<?php

namespace Tests\Setting;

use Codeages\Biz\Framework\Testing\DatabaseSeeder;

class SettingSeeder extends DatabaseSeeder
{
    public function run($isRun = true)
    {
        $rows = array(
            array(
                'id' => 1,
                'name' => 'with_array_value',
                'data' => serialize(array(
                    'key1' => 'value1',
                    'key2' => 'value2',
                )),
            ),
            array(
                'id' => 2,
                'name' => 'with_string_value',
                'data' => serialize('this is astring value'),
            ),
            array(
                'id' => 3,
                'name' => 'with_int_value',
                'data' => serialize(0),
            ),
            array(
                'id' => 4,
                'name' => 'dot_key',
                'data' => serialize(array(
                    'subkey' => 'value',
                )),
            ),
        );

        return $this->insertRows('biz_setting', $rows, $isRun);
    }
}
