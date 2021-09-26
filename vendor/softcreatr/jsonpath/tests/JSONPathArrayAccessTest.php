<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

namespace Flow\JSONPath\Test;

use Exception;
use Flow\JSONPath\JSONPath;
use Flow\JSONPath\Test\Traits\TestDataTrait;

class JSONPathArrayAccessTest extends TestCase
{
    use TestDataTrait;

    /**
     * @throws Exception
     */
    public function testChaining()
    {
        $jsonPath = (new JSONPath($this->getData('conferences')));

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
    public function testIterating()
    {
        $data = $this->getData('conferences');

        $conferences = (new JSONPath($data))
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
     * @throws Exception
     */
    public function testDifferentStylesOfAccess()
    {
        $data = (new JSONPath($this->getData('conferences', mt_rand(0, 1))));

        self::assertArrayHasKey('conferences', $data);

        $conferences = $data->__get('conferences')->getData();

        if (is_array($conferences[0])) {
            self::assertEquals('Western Conference', $conferences[0]['name']);
        } else {
            self::assertEquals('Western Conference', $conferences[0]->name);
        }
    }
}
