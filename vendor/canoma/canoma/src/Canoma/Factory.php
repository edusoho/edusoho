<?php

namespace Canoma;

/**
 * @author Mark van der Velden <mark@dynom.nl>
 */
class Factory
{
    const CONF_REPLICA_COUNT = 'replica_count';
    const CONF_HASHING_ADAPTER = 'hashing_adapter';
    const CONF_NODES = 'nodes';


    /**
     * Create a manager and the adapter
     *
     * @param array $configuration
     * @return \Canoma\Manager
     * @throws \InvalidArgumentException
     */
    public function createManager(array $configuration)
    {
        if ( ! $this->testManagerConfiguration($configuration)) {
            throw new \InvalidArgumentException(
                'Invalid configuration. Either one or more parameters are missing or contain invalid values.'
            );
        }

        $manager = new Manager(
            $this->createAdapter($configuration),
            $configuration[static::CONF_REPLICA_COUNT]
        );

        // If we have >0 nodes, add them to our manager
        if (!empty($configuration[static::CONF_NODES])) {
            $manager->addNodes($configuration[static::CONF_NODES]);
        }

        return $manager;
    }


    /**
     * Create an adapter as specified by the configuration.
     *
     * @param array $configuration
     * @return \Canoma\HashAdapterInterface
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function createAdapter(array $configuration)
    {
        if ( ! $this->testAdapterConfiguration($configuration)) {
            throw new \InvalidArgumentException(
                'Invalid configuration. No adapter has been specified.'
            );
        }

        $adapterClassName = __NAMESPACE__ . '\\HashAdapter\\'. $configuration[static::CONF_HASHING_ADAPTER];
        if ( ! class_exists($adapterClassName)) {
            throw new \RuntimeException(
                'Invalid configuration. The specified adapter "'. $adapterClassName .'" could not be created.'
            );
        }

        return new $adapterClassName($configuration);
    }


    /**
     * Test if the configuration for a manager is valid. Returns true when all required properties have been correctly
     * specified.
     *
     * @param array $configuration
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function testManagerConfiguration(array $configuration)
    {

        if ( ! isset($configuration[static::CONF_REPLICA_COUNT])) {
            return false;
        }

        if ( ! is_numeric($configuration[static::CONF_REPLICA_COUNT])
                || $configuration[static::CONF_REPLICA_COUNT] < 0) {
            return false;
        }

        return true;
    }


    /**
     * Test if the configuration is valid for an adapter. Returns true when all required properties have been correctly
     * specified.
     *
     * @param array $configuration
     * @return bool
     */
    public function testAdapterConfiguration(array $configuration)
    {
        if ( ! isset($configuration[static::CONF_HASHING_ADAPTER])) {
            return false;
        }

        return true;
    }

}
