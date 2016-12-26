<?php

namespace Flexihash\Tests\Hasher;

use Flexihash\Hasher\HasherInterface;

/**
 * @author Paul Annesley
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class MockHasher implements HasherInterface
{
    private $hashValue;

    public function setHashValue($hash)
    {
        $this->hashValue = $hash;
    }

    public function hash($value)
    {
        return $this->hashValue;
    }
}
