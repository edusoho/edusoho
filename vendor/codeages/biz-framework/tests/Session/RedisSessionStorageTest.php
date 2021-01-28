<?php

namespace Tests;

class RedisSessionStorageTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->biz['session.options'] = array(
            'max_life_time' => 1,
            'session_storage' => 'redis',
        );
    }

    public function testCreate()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionService()->saveSession($mockedSession);

        $keys = array_keys($mockedSession);
        foreach ($keys as $key) {
            $this->assertEquals($mockedSession[$key], $session[$key]);
        }
    }

    public function testCreateWithSql()
    {
        $mockedSessionWithSql = $this->mockSessionWithSql();
        $session = $this->getSessionService()->saveSession($mockedSessionWithSql);

        $keys = array_keys($mockedSessionWithSql);
        foreach ($keys as $key) {
            $this->assertEquals($mockedSessionWithSql[$key], $session[$key]);
        }
    }

    public function testUpdateSessionBySessId()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionService()->saveSession($mockedSession);

        sleep(1);

        $session['sess_data'] = 'test';
        $updatedSession = $this->getSessionService()->saveSession($session);
        $keys = array_keys($mockedSession);
        foreach ($keys as $key) {
            if (in_array($key, array('sess_data', 'sess_time'))) {
                continue;
            }
            $this->assertEquals($mockedSession[$key], $session[$key]);
        }

        $this->assertNotEquals($mockedSession['sess_data'], $updatedSession['sess_data']);
        $this->assertNotEmpty($updatedSession['sess_time']);
    }

    public function testUpdateSessionBySessIdWithSql()
    {
        $mockedSession = $this->mockSessionWithSql();
        $session = $this->getSessionService()->saveSession($mockedSession);

        sleep(1);

        $session['sess_data'] = 'test';
        $updatedSession = $this->getSessionService()->saveSession($session);
        $keys = array_keys($mockedSession);
        foreach ($keys as $key) {
            if (in_array($key, array('sess_data', 'sess_time'))) {
                continue;
            }
            $this->assertEquals($mockedSession[$key], $session[$key]);
        }

        $this->assertNotEquals($mockedSession['sess_data'], $updatedSession['sess_data']);
        $this->assertNotEmpty($updatedSession['sess_time']);
    }

    public function testDeleteSession()
    {
        $mockedSession = $this->mockSession();
        $session = $this->getSessionService()->saveSession($mockedSession);
        $this->getSessionService()->deleteSessionBySessId($session['sess_id']);

        $deleteSession = $this->getSessionService()->getSessionBySessId($session['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    public function testDeleteSessionWithSql()
    {
        $mockedSession = $this->mockSessionWithSql();
        $session = $this->getSessionService()->saveSession($mockedSession);
        $this->getSessionService()->deleteSessionBySessId($session['sess_id']);

        $deleteSession = $this->getSessionService()->getSessionBySessId($session['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    public function testGc()
    {
        $mockedSession = $this->mockSession();
        $this->getSessionService()->saveSession($mockedSession);

        sleep(2);

        $this->getSessionService()->gc();
        $deleteSession = $this->getSessionService()->getSessionBySessId($mockedSession['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    public function testGcWithSql()
    {
        $mockedSession = $this->mockSessionWithSql();
        $this->getSessionService()->saveSession($mockedSession);

        sleep(2);

        $this->getSessionService()->gc();
        $deleteSession = $this->getSessionService()->getSessionBySessId($mockedSession['sess_id']);
        $this->assertEmpty($deleteSession);
    }

    protected function mockSession()
    {
        return array(
            'sess_id' => 'sess'.rand(1000000, 9000000),
            'sess_data' => 'ababa',
        );
    }

    protected function mockSessionWithSql()
    {
        return array(
            'sess_id' => 'sess_'.rand(1000000, 9000000).'"1\' OR \'1\'=\'1"',
            'sess_data' => 'sql',
        );
    }

    protected function getSessionService()
    {
        return $this->biz->service('Session:SessionService');
    }
}
