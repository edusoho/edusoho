<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Test;

use Flow\JSONPath\{JSONPath, JSONPathException};
use PHPUnit\Framework\TestCase;

class JSONPathDashedIndexTest extends TestCase
{
    /**
     * @return array[]
     */
    public function indexDataProvider(): array
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
     * @throws JSONPathException
     */
    public function testSlice(string $path, array $data, array $expected): void
    {
        $results = (new JSONPath($data))
            ->find($path);

        self::assertEquals($expected, $results->getData());
    }
}
