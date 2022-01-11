<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

namespace Flow\JSONPath\Test;

use Flow\JSONPath\JSONPath;
use Flow\JSONPath\JSONPathException;

class JSONPathDashedIndexTest extends TestCase
{
    /**
     * @return array[]
     */
    public function indexDataProvider()
    {
        return [
            [
                '$.data[test-test-test]',
                ['data' => ['test-test-test' => 'foo']],
                ['foo'],
            ],
            [
                '$.data[40f35757-2563-4790-b0b1-caa904be455f]',
                ['data' => ['40f35757-2563-4790-b0b1-caa904be455f' => 'bar']],
                ['bar'],
            ],
        ];
    }

    /**
     * @dataProvider indexDataProvider
     *
     * @param string $path
     * @param array $data
     * @param array $expected
     *
     * @throws JSONPathException
     */
    public function testSlice($path, array $data, array $expected)
    {
        $results = (new JSONPath($data))
            ->find($path);

        self::assertEquals($expected, $results->getData());
    }
}
