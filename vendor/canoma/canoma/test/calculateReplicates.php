<?php
/**
 * The standard deviation test script.
 *
 * Call like:
 *
 *     php calculateReplicates.php [<number of replicates>]
 *
 *
 * This script is used to test about what settings you want to use in your project. There are a couple of parameters you
 * might want to fiddle with:
 *
 * - The hashing adapter
 * - The amount of replicates (the amount of aliases for a node)
 * - The amount of nodes (servers)
 * - The size of your cache and node keys (amount of characters of the argument for 'addNode' and 'getNodeForString')
 * - The amount of cache-keys (estimated amount of keys)
 *
 *
 * Suggested approach:
 *
 * - Obtain the amount of keys of your system (guesstimate it for your new or read them from, your existing storage systems)
 * - Obtain a couple of average sized key names
 * - Obtain the amount of nodes, and their host-names, of your system
 * - Tinker with the replicates and algorithm settings until you find an acceptable configuration
 * - Make a longer term prognosis and enter the same data, make a plan when (not if) you need to change your configuration
 *
 *
 * Want to learn more? Be sure to read the documentation on: https://github.com/Dynom/Canoma
 *
 * Have fun!
 */
require __DIR__ .'/bootstrap.php';

// The number and the algorithm you want to play with
$replicates = (int) (isset($argv[1]) ? $argv[1] : 60);
$adapter = new \Canoma\HashAdapter\Md5();
$nodes = 5;

// The amount of cache-keys we'll be storing to simulate. Pick a real-world-scenario number, don't aim too high or
// too low for your initial situation.
$cacheKeys = 100 * 1000;

// ---------------------------------------------------------------------------------------------------------------------

// Create our manager
$manager = new \Canoma\Manager(
    $adapter,
    $replicates
);


// Adding our nodes
for ($i = 0; $i < $nodes; $i++) {

    // It's just an example. Pick host names and use a fully qualified connection string if possible
    $manager->addNode('tcp://cache'. $i .'.pool0.example.org');
}


// A run summary
echo "Running with the following parameters:
    Replicates: $replicates
    Adapter: ". get_class($adapter) ."
    Nodes: ". count($manager->getAllNodes()) ." (The amount of cache-servers that will be storing your data)
    Total ring positions: ". count($manager->getAllPositions()) ."
    Cache keys: $cacheKeys
";



// Some algorithms (like Adler32) won't work well on "small" key-names, so experiment with it.
// Pick a couple of nice, average, key-names you use in your stack.
// As a side-note: It's considered good-practise to include a version number in your cache-keys. It allows for easy
// purging and pre-heating of your cache
$keyNameIterator = new \InfiniteIterator(
    new ArrayIterator(
        array(
             'v421:system:entity:user:',
             'v421:system:entity:shopping-cart:',
             'v421:system:meta-data:entities:',
             'v421:job-queue:jobs:',
             'v421:job-queue:job:',
             'v421:statistics:search-queries:',
             // .. Add more, or less, if you want ..
        )
    )
);


// Doing the lookup
$result = array();
for ($i = 0; $i < $cacheKeys; $i++) {

    // Picking a key-name from list
    $keyName = $keyNameIterator->current();
    $keyNameIterator->next();

    // Obtaining the node for the <key + variable>
    $node = $manager->getNodeForString($keyName . $i);

    if ( ! isset($result[$node])) {
        $result[$node] = 0;
    }

    $result[$node]++;
}


// The result
printf("\n\n===> Standard deviation: %0.2f\n\n", calculateStandardDeviation($result));
echo "The more evenly spread the numbers are, the better.\n";
print_r($result);


if (count($result) < count($manager->getAllNodes())) {
    echo "!!! WARNING !!!\n",
        "not all nodes in the pool have been used. The entropy for the chosen algorithm is probably too little. ",
        "Quite possibly the key-size is too small.\n",
        "Try a different algorithm (or larger key-names).",
        "\n";
}



/**
 * @param array $result
 * @return float
 */
function calculateStandardDeviation(array $result)
{
    $mean = array_sum($result) / count($result);

    $deviationResult = array();
    foreach ($result as $nodeCount) {
        $deviationResult[] = pow($nodeCount - $mean, 2);
    }

    return sqrt(array_sum($deviationResult) / count($deviationResult));
}
