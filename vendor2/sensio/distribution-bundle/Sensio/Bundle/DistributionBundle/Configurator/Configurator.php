<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\DistributionBundle\Configurator;

use Sensio\Bundle\DistributionBundle\Configurator\Step\StepInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Configurator.
 *
 * @author Marc Weistroff <marc.weistroff@gmail.com>
 * @author Jérôme Vieilledent <lolautruche@gmail.com>
 */
class Configurator
{
    protected $filename;
    protected $steps;
    protected $sortedSteps;
    protected $parameters;

    public function __construct($kernelDir)
    {
        $this->kernelDir = $kernelDir;
        $this->filename = $kernelDir.'/config/parameters.yml';

        $this->steps = array();
        $this->parameters = $this->read();
    }

    public function isFileWritable()
    {
        return is_writable($this->filename);
    }

    public function clean()
    {
        if (file_exists($this->getCacheFilename())) {
            @unlink($this->getCacheFilename());
        }
    }

    /**
     * @param StepInterface $step
     * @param int           $priority
     */
    public function addStep(StepInterface $step, $priority = 0)
    {
        if (!isset($this->steps[$priority])) {
            $this->steps[$priority] = array();
        }

        $this->steps[$priority][] = $step;
        $this->sortedSteps = null;
    }

    /**
     * @param int $index
     *
     * @return StepInterface
     */
    public function getStep($index)
    {
        $steps = $this->getSteps();
        if (isset($steps[$index])) {
            return $steps[$index];
        }
    }

    /**
     * @return StepInterface[]
     */
    public function getSteps()
    {
        if ($this->sortedSteps === null) {
            $this->sortedSteps = $this->getSortedSteps();
            foreach ($this->sortedSteps as $step) {
                $step->setParameters($this->parameters);
            }
        }

        return $this->sortedSteps;
    }

    /**
     * Sort routers by priority.
     * The highest priority number is the highest priority (reverse sorting).
     *
     * @return StepInterface[]
     */
    private function getSortedSteps()
    {
        $sortedSteps = array();
        krsort($this->steps);

        foreach ($this->steps as $steps) {
            $sortedSteps = array_merge($sortedSteps, $steps);
        }

        return $sortedSteps;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return int
     */
    public function getStepCount()
    {
        return count($this->getSteps());
    }

    /**
     * @param array $parameters
     */
    public function mergeParameters($parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        $majors = array();
        foreach ($this->getSteps() as $step) {
            foreach ($step->checkRequirements() as $major) {
                $majors[] = $major;
            }
        }

        return $majors;
    }

    /**
     * @return array
     */
    public function getOptionalSettings()
    {
        $minors = array();
        foreach ($this->getSteps() as $step) {
            foreach ($step->checkOptionalSettings() as $minor) {
                $minors[] = $minor;
            }
        }

        return $minors;
    }

    /**
     * Renders parameters as a string.
     *
     * @param int $expanded
     *
     * @return string
     */
    public function render($expanded = 10)
    {
        return Yaml::dump(array('parameters' => $this->parameters), $expanded);
    }

    /**
     * Writes parameters to parameters.yml or temporary in the cache directory.
     *
     * @param int $expanded
     *
     * @return int
     */
    public function write($expanded = 10)
    {
        $filename = $this->isFileWritable() ? $this->filename : $this->getCacheFilename();

        return file_put_contents($filename, $this->render($expanded));
    }

    /**
     * Reads parameters from file.
     *
     * @return array
     */
    protected function read()
    {
        $filename = $this->filename;
        if (!$this->isFileWritable() && file_exists($this->getCacheFilename())) {
            $filename = $this->getCacheFilename();
        }

        if (!file_exists($filename)) {
            return array();
        }

        $ret = Yaml::parse(file_get_contents($filename));
        if (false === $ret || array() === $ret) {
            throw new \InvalidArgumentException(sprintf('The %s file is not valid.', $filename));
        }

        if (isset($ret['parameters']) && is_array($ret['parameters'])) {
            return $ret['parameters'];
        } else {
            return array();
        }
    }

    /**
     * getCacheFilename.
     *
     * @return string
     */
    protected function getCacheFilename()
    {
        return $this->kernelDir.'/cache/parameters.yml';
    }
}
