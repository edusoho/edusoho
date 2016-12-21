<?php

namespace Flexihash;

use Flexihash\Hasher\HasherInterface;
use Flexihash\Hasher\Crc32Hasher;

/**
 * A simple consistent hashing implementation with pluggable hash algorithms.
 *
 * @author Paul Annesley
 * @license http://www.opensource.org/licenses/mit-license.php
 */
class Flexihash
{
    /**
     * The number of positions to hash each target to.
     *
     * @var int
     */
    private $replicas = 64;

    /**
     * The hash algorithm, encapsulated in a Flexihash_Hasher implementation.
     * @var object Flexihash_Hasher
     */
    private $hasher;

    /**
     * Internal counter for current number of targets.
     * @var int
     */
    private $targetCount = 0;

    /**
     * Internal map of positions (hash outputs) to targets.
     * @var array { position => target, ... }
     */
    private $positionToTarget = [];

    /**
     * Internal map of targets to lists of positions that target is hashed to.
     * @var array { target => [ position, position, ... ], ... }
     */
    private $targetToPositions = [];

    /**
     * Whether the internal map of positions to targets is already sorted.
     * @var bool
     */
    private $positionToTargetSorted = false;

    /**
     * Constructor.
     * @param Flexihash\Hasher\HasherInterface $hasher
     * @param int $replicas Amount of positions to hash each target to.
     */
    public function __construct(HasherInterface $hasher = null, $replicas = null)
    {
        $this->hasher = $hasher ? $hasher : new Crc32Hasher();
        if (!empty($replicas)) {
            $this->replicas = $replicas;
        }
    }

    /**
     * Add a target.
     * @param string $target
     * @param float $weight
     * @chainable
     */
    public function addTarget($target, $weight = 1)
    {
        if (isset($this->targetToPositions[$target])) {
            throw new Exception("Target '$target' already exists.");
        }

        $this->targetToPositions[$target] = [];

        // hash the target into multiple positions
        for ($i = 0; $i < round($this->replicas * $weight); ++$i) {
            $position = $this->hasher->hash($target.$i);
            $this->positionToTarget[$position] = $target; // lookup
            $this->targetToPositions[$target] [] = $position; // target removal
        }

        $this->positionToTargetSorted = false;
        ++$this->targetCount;

        return $this;
    }

    /**
     * Add a list of targets.
     *
     * @param array $targets
     * @param float $weight
     * @return self fluent
     */
    public function addTargets($targets, $weight = 1)
    {
        foreach ($targets as $target) {
            $this->addTarget($target, $weight);
        }

        return $this;
    }

    /**
     * Remove a target.
     *
     * @param string $target
     * @return self fluent
     * @throws Flexihash\Exception when target does not exist
     */
    public function removeTarget($target)
    {
        if (!isset($this->targetToPositions[$target])) {
            throw new Exception("Target '$target' does not exist.");
        }

        foreach ($this->targetToPositions[$target] as $position) {
            unset($this->positionToTarget[$position]);
        }

        unset($this->targetToPositions[$target]);

        --$this->targetCount;

        return $this;
    }

    /**
     * A list of all potential targets.
     * @return array
     */
    public function getAllTargets()
    {
        return array_keys($this->targetToPositions);
    }

    /**
     * Looks up the target for the given resource.
     * @param string $resource
     * @return string
     * @throws Flexihash\Exception when no targets defined
     */
    public function lookup($resource)
    {
        $targets = $this->lookupList($resource, 1);
        if (empty($targets)) {
            throw new Exception('No targets exist');
        }

        return $targets[0];
    }

    /**
     * Get a list of targets for the resource, in order of precedence.
     * Up to $requestedCount targets are returned, less if there are fewer in total.
     *
     * @param string $resource
     * @param int $requestedCount The length of the list to return
     * @return array List of targets
     * @throws Flexihash\Exception when count is invalid
     */
    public function lookupList($resource, $requestedCount)
    {
        if (!$requestedCount) {
            throw new Exception('Invalid count requested');
        }

        // handle no targets
        if (empty($this->positionToTarget)) {
            return [];
        }

        // optimize single target
        if ($this->targetCount == 1) {
            return array_unique(array_values($this->positionToTarget));
        }

        // hash resource to a position
        $resourcePosition = $this->hasher->hash($resource);

        $results = [];
        $collect = false;

        $this->sortPositionTargets();

        // search values above the resourcePosition
        foreach ($this->positionToTarget as $key => $value) {
            // start collecting targets after passing resource position
            if (!$collect && $key > $resourcePosition) {
                $collect = true;
            }

            // only collect the first instance of any target
            if ($collect && !in_array($value, $results)) {
                $results [] = $value;
            }

            // return when enough results, or list exhausted
            if (count($results) == $requestedCount || count($results) == $this->targetCount) {
                return $results;
            }
        }

        // loop to start - search values below the resourcePosition
        foreach ($this->positionToTarget as $key => $value) {
            if (!in_array($value, $results)) {
                $results [] = $value;
            }

            // return when enough results, or list exhausted
            if (count($results) == $requestedCount || count($results) == $this->targetCount) {
                return $results;
            }
        }

        // return results after iterating through both "parts"
        return $results;
    }

    public function __toString()
    {
        return sprintf(
            '%s{targets:[%s]}',
            get_class($this),
            implode(',', $this->getAllTargets())
        );
    }

    // ----------------------------------------
    // private methods

    /**
     * Sorts the internal mapping (positions to targets) by position.
     */
    private function sortPositionTargets()
    {
        // sort by key (position) if not already
        if (!$this->positionToTargetSorted) {
            ksort($this->positionToTarget, SORT_REGULAR);
            $this->positionToTargetSorted = true;
        }
    }
}
