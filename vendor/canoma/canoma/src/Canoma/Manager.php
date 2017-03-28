<?php

namespace Canoma;

/**
 * @author Mark van der Velden <mvdvelden@ibuildings.nl>
 */
class Manager
{
    /**
     * @var HashAdapterInterface
     */
    private $adapter;

    /**
     * @var int
     */
    private $replicaCount;

    /**
     * The list with cache nodes
     *
     * @var array
     */
    private $nodes = array();

    /**
     * The ring positions
     *
     * @var array
     */
    private $nodePositions = array();

    /**
     * The positions, per node.
     *
     * @var array
     */
    private $positionsPerNode = array();


    /**
     * Construct the manager, requiring an adapter and a replica count of 0 or more.
     *
     * @param HashAdapterInterface $adapter
     * @param int $replicaCount
     */
    public function __construct(HashAdapterInterface $adapter, $replicaCount)
    {
        $this->adapter = $adapter;
        $this->replicaCount = (int) $replicaCount;
    }


    /**
     * Return a matching node, based on a string
     *
     * @param string $string
     * @return mixed
     */
    public function getNodeForString($string)
    {
        $stringPosition = $this->adapter->hash($string);

        // Find the node, that is positioned after the position of the string.
        foreach ($this->nodePositions as $nodePosition => $node) {

            // If the position of the node, is greater than the position of the string, we can return the first hit.
            if ($this->adapter->compare($nodePosition, $stringPosition) > 0) {
                return $node;
            }
        }

        // If we reached the end of our list and still didn't find a suitable node, we pick the first one
        // since that is first one in line in our circle
        return reset($this->nodePositions);
    }


    /**
     * @param $string
     * @param int $amount
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getMultipleNodesForString($string, $amount = 2)
    {
        $stringPosition = $this->adapter->hash($string);
        $amount = is_numeric($amount) ? (int) $amount : -1;

        if ( ! $this->testValidAmount($amount)) {
            throw new \RuntimeException(
                'Invalid amount has been given, fewer nodes have been added or the value is too low (>0).'
            );
        }

        // Don't bother looping if the amount is identical. This is, however, a silly situation...
        if (count($this->nodes) === $amount) {
            return $this->nodes;
        }

        // Get all the nodes from our starting position
        $nodes = $this->findMultipleNodesAfterPosition($stringPosition, $amount);

        // If we have too few, fetch the remaining from the start.
        if (count($nodes) < $amount) {
            $remaining = $amount - count($nodes);
            $nodes = array_merge(
                $nodes,
                $this->findMultipleNodesFromStart($remaining)
            );
        }

        return $nodes;
    }


    /**
     * Returns true if a node has been defined, false otherwise.
     *
     * @param string $node
     * @return bool
     */
    public function hasNode($node)
    {
        return isset($this->nodes[ $node ]) && isset($this->positionsPerNode[ $node ]);
    }


    /**
     * Add a cache-node. The method expects a string argument, representing a node.
     *
     * @param string $node
     * @return Manager
     * @throws \RuntimeException
     */
    public function addNode($node)
    {
        // Sanity check, we only support string types
        if ( ! is_string($node)) {
            throw new \RuntimeException('Expecting a string argument, but $node is not string!');
        }

        if (isset($this->nodes[ $node ])) {
            throw new \RuntimeException('Node already added.');
        }

        // Calculating all positions
        $nodePositions = $this->getNodePositions($node, $this->replicaCount);

        // Storing the positions for this node
        $this->positionsPerNode[ $node ] = $nodePositions;

        // Adding the positions to the 'ring'
        $this->nodePositions = $this->nodePositions + $nodePositions;

        // Sort the keys
        ksort($this->nodePositions);

        // Adding the node to the list
        $this->nodes[ $node ] = $node;

        return $this;
    }


    /**
     * Add multiple nodes at once.
     *
     * @param array $nodes
     * @return Manager
     */
    public function addNodes(array $nodes)
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        return $this;
    }


    /**
     * Remove a node from the list.
     *
     * @param string $node
     * @return Manager
     * @throws \RuntimeException
     */
    public function removeNode($node)
    {
        if ( ! $this->hasNode($node)) {
            throw new \RuntimeException('Node "'. $node .'" has not been defined.');
        }

        // Get the node positions
        $nodePositions = $this->positionsPerNode[ $node ];

        // Remove the node positions from the ring
        foreach ($nodePositions as $position => $node) {
            unset($this->nodePositions[ $position ]);
        }

        // Unset the node positions from the lookup index
        unset($this->positionsPerNode[ $node ]);

        // Unset the node reference
        unset($this->nodes[ $node ]);


        return $this;
    }


    /**
     * Return the complete list with nodes
     *
     * @return array
     */
    public function getAllNodes()
    {
        return $this->nodes;
    }


    /**
     * Return a positions of a node
     *
     * @param string $node
     * @return array
     * @throws \RuntimeException
     */
    public function getPositionsOfNode($node)
    {
        if ( ! isset($this->positionsPerNode[$node])) {
            throw new \RuntimeException('Invalid node supplied, no such node has been added.');
        }

        return $this->positionsPerNode[$node];
    }


    /**
     * Return all node positions
     *
     * @return array
     */
    public function getAllPositions()
    {
        return $this->nodePositions;
    }


    /**
     * @return HashAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }


    /**
     * Return hashes based on the node and the amount of replicas to create.
     *
     * @param string $node
     * @param int $replicaCount
     * @return array
     */
    private function getNodePositions($node, $replicaCount)
    {
        $positions = array();
        for ($i=0; $i < $replicaCount; $i++) {

            // Using a happy separator, since it's unlikely to be used in connection strings
            // It is, however, still possible to have a collision...
            $replicaPosition = $this->adapter->hash("$i^_^$node");
            $positions[$replicaPosition] = $node;
        }

        return $positions;
    }


    /**
     * @param $amount
     *
     * @return bool
     */
    private function testValidAmount($amount)
    {
        // Sanity checks
        if ($amount < 1) {
            return false;
        }

        if (count($this->nodes) < $amount) {
            return false;
        }

        return true;
    }


    /**
     * Find $amount of nodes, starting from position $offsetPosition.
     *
     * @param int|float $offsetPosition
     * @param int $amount
     *
     * @return array
     */
    private function findMultipleNodesAfterPosition($offsetPosition, $amount)
    {
        $nodes = array();

        // Find the nodes, that are positioned after the position of $string.
        foreach ($this->nodePositions as $nodePosition => $node) {

            // If the position of the node, is greater than the position of the string, we can return the first hit.
            if ($this->adapter->compare($nodePosition, $offsetPosition) > 0) {
                $nodes[$node] = $node;

                if (count($nodes) === $amount) {
                    return $nodes;
                }
            }
        }

        return $nodes;
    }


    /**
     * Find $amount of nodes, starting from position 0
     *
     * @param int $amount
     *
     * @return array
     */
    private function findMultipleNodesFromStart($amount)
    {
        $amount = (int) $amount;
        $nodes = array();


        // Find the node, that is positioned after the position at the start
        foreach ($this->nodePositions as $node) {

            $nodes[$node] = $node;

            if (count($nodes) === $amount) {
                return $nodes;
            }
        }

        return $nodes;
    }
}
