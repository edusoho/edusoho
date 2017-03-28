<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ConsistantHashTest extends TestCase
{
    public function testHash()
    {
        $hash = new \Canoma\Manager(
            new \Canoma\HashAdapter\Md5,
            30
        );

        $servers = array(
            array('host'=>'192.168.1.1', 'port'=>8080),
            array('host'=>'192.168.1.12', 'port'=>8080),
            array('host'=>'192.168.1.23', 'port'=>8080),
            array('host'=>'192.168.1.34', 'port'=>8080),
            array('host'=>'192.168.1.45', 'port'=>8080),
        );

        $nodes = array();
        foreach ($servers as $key => $value) {
            $hash->addNode($value['host'].':'.$value['port']);
            $nodes[] = $value['host'].':'.$value['port'];
        }

        for ($i=0; $i < 1000; $i++) {
            $key = $this->getRandomString(rand(1, 100));
            $node = $hash->getNodeForString($key);
            $this->assertTrue(in_array($node, $nodes));

            for ($j=0; $j < 100; $j++) {
                $node1 = $hash->getNodeForString($key);
                $this->assertEquals($node, $node1);
                $this->assertTrue(in_array($node1, $nodes));
            }
        }
    }

    protected function getRandomString($length, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        $s = '';
        $cLength = strlen($chars);

        while (strlen($s) < $length) {
            $s .= $chars[mt_rand(0, $cLength - 1)];
        }

        return $s;
    }
}
