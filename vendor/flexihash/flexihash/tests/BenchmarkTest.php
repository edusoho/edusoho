<?php

namespace Flexihash\Tests;

use PHPUnit_Framework_TestCase;
use Flexihash\Flexihash;
use Flexihash\Hasher\Crc32Hasher;
use Flexihash\Hasher\Md5Hasher;

/**
 * Benchmarks, not really tests.
 *
 * @author Paul Annesley
 * @group benchmark
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class BenchmarkTest extends PHPUnit_Framework_TestCase
{
    private $targets = 10;
    private $lookups = 1000;

    public function dump($message)
    {
        echo $message."\n";
    }

    public function testAddTargetWithNonConsistentHash()
    {
        $results1 = [];
        foreach (range(1, $this->lookups) as $i) {
            $results1[$i] = $this->basicHash("t$i", 10);
        }

        $results2 = [];
        foreach (range(1, $this->lookups) as $i) {
            $results2[$i] = $this->basicHash("t$i", 11);
        }

        $differences = 0;
        foreach (range(1, $this->lookups) as $i) {
            if ($results1[$i] !== $results2[$i]) {
                ++$differences;
            }
        }

        $percent = round($differences / $this->lookups * 100);

        $this->dump("NonConsistentHash: {$percent}% of lookups changed ".
            "after adding a target to the existing {$this->targets}");
    }

    public function testRemoveTargetWithNonConsistentHash()
    {
        $results1 = [];
        foreach (range(1, $this->lookups) as $i) {
            $results1[$i] = $this->basicHash("t$i", 10);
        }

        $results2 = [];
        foreach (range(1, $this->lookups) as $i) {
            $results2[$i] = $this->basicHash("t$i", 9);
        }

        $differences = 0;
        foreach (range(1, $this->lookups) as $i) {
            if ($results1[$i] !== $results2[$i]) {
                ++$differences;
            }
        }

        $percent = round($differences / $this->lookups * 100);

        $this->dump("NonConsistentHash: {$percent}% of lookups changed ".
            "after removing 1 of {$this->targets} targets");
    }

    public function testHopeAddingTargetDoesNotChangeMuchWithCrc32Hasher()
    {
        $hashSpace = new Flexihash(
            new Crc32Hasher()
        );
        foreach (range(1, $this->targets) as $i) {
            $hashSpace->addTarget("target$i");
        }

        $results1 = [];
        foreach (range(1, $this->lookups) as $i) {
            $results1[$i] = $hashSpace->lookup("t$i");
        }

        $hashSpace->addTarget('target-new');

        $results2 = [];
        foreach (range(1, $this->lookups) as $i) {
            $results2[$i] = $hashSpace->lookup("t$i");
        }

        $differences = 0;
        foreach (range(1, $this->lookups) as $i) {
            if ($results1[$i] !== $results2[$i]) {
                ++$differences;
            }
        }

        $percent = round($differences / $this->lookups * 100);

        $this->dump("ConsistentHash: {$percent}% of lookups changed ".
            "after adding a target to the existing {$this->targets}");
    }

    public function testHopeRemovingTargetDoesNotChangeMuchWithCrc32Hasher()
    {
        $hashSpace = new Flexihash(
            new Crc32Hasher()
        );
        foreach (range(1, $this->targets) as $i) {
            $hashSpace->addTarget("target$i");
        }

        $results1 = [];
        foreach (range(1, $this->lookups) as $i) {
            $results1[$i] = $hashSpace->lookup("t$i");
        }

        $hashSpace->removeTarget('target1');

        $results2 = [];
        foreach (range(1, $this->lookups) as $i) {
            $results2[$i] = $hashSpace->lookup("t$i");
        }

        $differences = 0;
        foreach (range(1, $this->lookups) as $i) {
            if ($results1[$i] !== $results2[$i]) {
                ++$differences;
            }
        }

        $percent = round($differences / $this->lookups * 100);

        $this->dump("ConsistentHash: {$percent}% of lookups changed ".
            "after removing 1 of {$this->targets} targets");
    }

    public function testHashDistributionWithCrc32Hasher()
    {
        $hashSpace = new Flexihash(
            new Crc32Hasher()
        );

        foreach (range(1, $this->targets) as $i) {
            $hashSpace->addTarget("target$i");
        }

        $results = [];
        foreach (range(1, $this->lookups) as $i) {
            $results[$i] = $hashSpace->lookup("t$i");
        }

        $distribution = [];
        foreach ($hashSpace->getAllTargets() as $target) {
            $distribution[$target] = count(array_keys($results, $target));
        }

        $this->dump(sprintf(
            'Distribution of %d lookups per target (min/max/median/avg): %d/%d/%d/%d',
            $this->lookups / $this->targets,
            min($distribution),
            max($distribution),
            round($this->median($distribution)),
            round(array_sum($distribution) / count($distribution))
        ));
    }

    public function testHasherSpeed()
    {
        $hashCount = 100000;

        $md5Hasher = new Md5Hasher();
        $crc32Hasher = new Crc32Hasher();

        $start = microtime(true);
        for ($i = 0; $i < $hashCount; ++$i) {
            $md5Hasher->hash("test$i");
        }
        $timeMd5 = microtime(true) - $start;

        $start = microtime(true);
        for ($i = 0; $i < $hashCount; ++$i) {
            $crc32Hasher->hash("test$i");
        }
        $timeCrc32 = microtime(true) - $start;

        $this->dump(sprintf(
            'Hashers timed over %d hashes (MD5 / CRC32): %f / %f',
            $hashCount,
            $timeMd5,
            $timeCrc32
        ));
    }

    // ----------------------------------------

    private function basicHash($value, $targets)
    {
        return abs(crc32($value) % $targets);
    }

    /**
     * @param array $array list of numeric values
     * @return numeric
     */
    private function median($values)
    {
        $values = array_values($values);
        sort($values);

        $count = count($values);
        $middleFloor = floor($count / 2);

        if ($count % 2 == 1) {
            return $values[$middleFloor];
        } else {
            return ($values[$middleFloor] + $values[$middleFloor + 1]) / 2;
        }
    }
}
