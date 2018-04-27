<?php

namespace AppBundle\Common;

class RandMachine
{
    private static $mockedRand = 0;

    public static function rand()
    {
        return empty(self::$mockedRand) ? rand() : self::$mockedRand;
    }

    public static function uniqidWithMtRand()
    {
        return empty(self::$mockedRand) ? uniqid(mt_rand()) : self::$mockedRand;
    }

    public static function setMockedRand($rand)
    {
        self::$mockedRand = $rand;
    }
}
