<?php

namespace Sensio\Bundle\GeneratorBundle\Model;

class EntityGeneratorResult
{
    /** @var string */
    private $entityPath;

    /** @var string */
    private $repositoryPath;

    /** @var string */
    private $mappingPath;

    /**
     * @param string $entityPath
     * @param string $repositoryPath
     * @param string $mappingPath
     */
    public function __construct($entityPath, $repositoryPath, $mappingPath)
    {
        $this->entityPath = $entityPath;
        $this->repositoryPath = $repositoryPath;
        $this->mappingPath = $mappingPath;
    }

    /**
     * @return string
     */
    public function getEntityPath()
    {
        return $this->entityPath;
    }

    /**
     * @return string
     */
    public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }

    /**
     * @return string
     */
    public function getMappingPath()
    {
        return $this->mappingPath;
    }
}
