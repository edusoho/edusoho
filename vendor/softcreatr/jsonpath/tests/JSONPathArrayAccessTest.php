<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Test;

use ArrayObject;
use Exception;
use Flow\JSONPath\JSONPath;
use Flow\JSONPath\Test\Traits\TestDataTrait;
use PHPUnit\Framework\TestCase;

use function is_array;

class JSONPathArrayAccessTest extends TestCase
{
    use TestDataTrait;

    /**
     * @throws Exception
     */
    public function testChaining(): void
    {
        $container = new ArrayObject($this->getData('conferences'));
        $jsonPath = new JSONPath($container);

        $teams = $jsonPath
            ->find('.conferences.*')
            ->find('..teams.*');

        self::assertEquals('Dodger', $teams[0]['name']);
        self::assertEquals('Mets', $teams[1]['name']);

        $teams = $jsonPath
            ->find('.conferences.*')
            ->find('..teams.*');

        self::assertEquals('Dodger', $teams[0]['name']);
        self::assertEquals('Mets', $teams[1]['name']);

        $teams = $jsonPath
            ->find('.conferences..teams.*');

        self::assertEquals('Dodger', $teams[0]['name']);
        self::assertEquals('Mets', $teams[1]['name']);
    }

    /**
     * @throws Exception
     */
    public function testIterating(): void
    {
        $container = new ArrayObject($this->getData('conferences'));

        $conferences = (new JSONPath($container))
            ->find('.conferences.*');

        $names = [];

        foreach ($conferences as $conference) {
            $players = $conference
                ->find('.teams.*.players[?(@.active=yes)]');

            foreach ($players as $player) {
                $names[] = $player->name;
            }
        }

        self::assertEquals(['Joe Face', 'something'], $names);
    }

    /**
     * @param bool $asArray
     * @testWith [false]
     *           [true]
     */
    public function testDifferentStylesOfAccess(bool $asArray): void
    {
        $container = new ArrayObject($this->getData('conferences', $asArray));
        $data = new JSONPath($container);

        self::assertArrayHasKey('conferences', $data);

        $conferences = $data->__get('conferences')->getData();

        if (is_array($conferences[0])) {
            self::assertEquals('Western Conference', $conferences[0]['name']);
        } else {
            self::assertEquals('Western Conference', $conferences[0]->name);
        }
    }

    public function testUpdate(): void
    {
        $container = new ArrayObject($this->getData('conferences'));
        $data = new JSONPath($container);

        $data->offsetSet('name', 'Major League Football');
        self::assertEquals('Major League Football', $data->name);
    }
}
