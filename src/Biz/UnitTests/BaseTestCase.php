<?php

namespace Biz\UnitTests;

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected static $biz;

    public static function setUpBeforeClass()
    {
    }

    public function setUp()
    {
        self::emptyDatabase();
    }

    public static function setBiz($biz)
    {
        self::$biz = $biz;
    }

    public static function emptyDatabaseQuickly()
    {
        $clear = new DatabaseDataClearer(self::$biz['db']);
        $clear->clearQuickly();
    }

    public static function emptyDatabase()
    {
        $clear = new DatabaseDataClearer(self::$biz['db']);
        $clear->clear();
    }
}
