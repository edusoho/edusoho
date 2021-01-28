<?php

namespace Codeages\RateLimiter;

use Codeages\RateLimiter\Storage\Storage;

class RateLimiter
{
    public $name;

    public $maxAllowance;

    public $period;

    private $storage;

    public function __construct($name, $maxAllowance, $period, Storage $storage)
    {
        $this->name = $name;
        $this->maxAllowance = $maxAllowance;
        $this->period = $period;
        $this->storage = $storage;
    }

    public function check($id, $use = 1.0)
    {
        $rate = $this->maxAllowance / $this->period;

        $key = $this->getKey($id);
        $value = $this->storage->get($key);
        if ($value !== false) {
            list($allowance, $lastCheckTime) = $this->unpackValue($value);

            $timePassed = time() - $lastCheckTime;
            $allowance += $timePassed * $rate;

            if ($allowance > $this->maxAllowance) {
                $allowance = $this->maxAllowance;
            }

            if ($allowance < $use) {
                $this->storage->set($key, $this->packValue($allowance, time()), $this->period);

                return 0;
            } else {
                $this->storage->set($key, $this->packValue($allowance - $use, time()), $this->period);

                return (int) floor($allowance);
            }
        } else {
            $this->storage->set($key, $this->packValue($this->maxAllowance - $use, time()), $this->period);

            return $this->maxAllowance;
        }
    }

    public function getAllowance($id)
    {
        $this->check($id, 0);
        $value = $this->storage->get($this->getKey($id));
        if ($value !== false) {
            list($allowance) = $this->unpackValue($value);

            return floor($allowance);
        }

        return $this->maxAllowance;
    }

    public function updateAllowance($id, $threshold)
    {
        $key = $this->getKey($id);
        $value = $this->storage->get($key);
        if ($value !== false) {
            list($allowance, $lastCheckTime) = $this->unpackValue($value);
            $updatedAllowance = ($allowance + $threshold) > 0 ? ($allowance + $threshold) : 0;
        } else {
            $updatedAllowance = $threshold > 0 ? $threshold : 0;
        }

        $this->storage->set($this->getKey($id), $this->packValue($updatedAllowance, time()), $this->period);

        return $updatedAllowance;
    }

    public function getMaxAllowance()
    {
        return $this->maxAllowance;
    }

    public function getAllow($id)
    {
        return $this->getAllowance($id);
    }

    public function purge($id)
    {
        $this->storage->del($this->getKey($id));
    }

    protected function packValue($allowance, $lastCheckTime)
    {
        return $allowance.','.$lastCheckTime;
    }

    protected function unpackValue($value)
    {
        return explode(',', $value, 2);
    }

    protected function getKey($id)
    {
        return 'rate-limit:'.$this->name.':'.$id;
    }
}
