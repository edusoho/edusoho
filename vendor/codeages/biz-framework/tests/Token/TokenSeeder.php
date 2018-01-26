<?php

namespace Tests\Token;

use Codeages\Biz\Framework\Testing\DatabaseSeeder;

class TokenSeeder extends DatabaseSeeder
{
    public function run($isRun = true)
    {
        $rows = array(
            array(
                'id' => 1,
                'place' => 'unit_test',
                '_key' => 'unit_test_key',
                'data' => '',
                'expired_time' => 0,
                'times' => 0,
                'remaining_times' => 0,
                'created_time' => time(),
            ),
            array(
                'id' => 2,
                'place' => 'unit_test',
                '_key' => 'unit_test_key_expired',
                'data' => '',
                'expired_time' => time() - 1,
                'times' => 0,
                'remaining_times' => 0,
                'created_time' => time(),
            ),
            array(
                'id' => 3,
                'place' => 'unit_test',
                '_key' => 'unit_test_key_no_expired',
                'data' => '',
                'expired_time' => time() + 10,
                'times' => 0,
                'remaining_times' => 0,
                'created_time' => time(),
            ),
            array(
                'id' => 4,
                'place' => 'unit_test',
                '_key' => 'unit_test_key_2_times',
                'data' => '',
                'expired_time' => 0,
                'times' => 2,
                'remaining_times' => 2,
                'created_time' => time(),
            ),
        );

        return $this->insertRows('biz_token', $rows, $isRun);
    }
}
