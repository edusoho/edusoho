<?php

/*
 * Copyright 2013 Benjamin Legendre
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace TiBeN\CrontabManager;

/**
 * CrontabRepository
 * 
 * Access and manage CrontabJob Objects (add, modify, delete). 
 * @author TiBeN
 */
class CrontabRepository
{
    private $crontabAdapter;
    private $crontabJobs = array();
    
    /**
     * Contain comments on the top of the crontab file. 
     *
     * @var String
     */
    public $headerComments;
    
    /**
     * Contain lines to not consider as cronjob when parsing
     * the crontab
     *
     * @var array
     */
    public $crontabLinesToBypass = array(

        // Prevent to parse the default Ubuntu crontab header example 
        '# 0 5 * * 1 tar -zcf /var/backups/home.tgz /home/'
    );

    /**
     * Instanciate a Crontab repository.
     * A CrontabAdapter adapter must be provided in order to communicate
     * with the system "crontab" command line.
     *
     * @param CrontabAdapter $crontabAdapter
     */
    public function __construct(CrontabAdapter $crontabAdapter)
    {
        $this->crontabAdapter = $crontabAdapter;
        $this->readCrontab();
    }
    
    /**
     * Return the CrontabJob in the "connected" crontab
     *
     * @return Array of CrontabJobs
     */
    public function getJobs()
    {
        return $this->crontabJobs;
    }
        
    /**
     * Finds jobs by matching theirs task commands with a regex
     *
     * @param String $regex
     * @throws InvalidArgumentException
     * @return Array of CronJobs
     */
    public function findJobByRegex($regex)
    {
        /* Test if regex is valid */
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \Exception($message);
        });
        
        try {
            preg_match($regex, 'test');
            restore_error_handler();
        } catch (\Exception $e) {
            restore_error_handler();
            throw new \InvalidArgumentException('Not a valid Regex : ' . $e->getMessage());
            return;
        }

        $crontabJobs = array();
        
        if (!empty($this->crontabJobs)) {
            foreach ($this->crontabJobs as $crontabJob) {
                if (preg_match($regex, $crontabJob->formatCrontabLine())) {
                    array_push($crontabJobs, $crontabJob);
                }
            }
        }
        
        return $crontabJobs;
    }
    
    /**
     * Add a new CrontabJob in the connected crontab
     *
     * @param CrontabJob $crontabJob
     */
    public function addJob(CrontabJob $crontabJob)
    {
        if (array_search($crontabJob, $this->crontabJobs) !== false) {
            $exceptionMessage = 'This job is already in the crontab. Please consider cloning the'
                . 'CrontabJob object if you want it to be registered twice.'
            ;
            throw new \LogicException($exceptionMessage);
        }
        array_push($this->crontabJobs, $crontabJob);
    }

    /**
     * Remove a CrontabJob from the connected crontab
     *
     * @param CrontabJob $crontabJob
     */
    public function removeJob(CrontabJob $crontabJob)
    {
        $jobKey = array_search($crontabJob, $this->crontabJobs, true);
        if ($jobKey === false) {
            throw new \LogicException('This job is not part of this crontab');
        }
        unset($this->crontabJobs[$jobKey]);
    }

    /**
     * Save all operations to the connected crontab.
     */
    public function persist()
    {
        $crontabRawData = '';
        if (!empty($this->headerComments)) {
            $crontabRawData .= $this->headerComments;
        }
        
        if (!empty($this->crontabJobs)) {
            foreach ($this->crontabJobs as $crontabJob) {
                try {
                    $crontabLine = $crontabJob->formatCrontabLine();
                    $crontabRawData .= ($crontabLine . "\n");
                } catch (Exception $e) {
                    /* Do nothing here */
                }
            }
        }
        
        $this->crontabAdapter->writeCrontab($crontabRawData);
    }
    
    /**
     * Retrieve the crontab raw data from the system then parse it.
     */
    private function readCrontab()
    {
        $crontabRawData = $this->crontabAdapter->readCrontab();
        
        if (empty($crontabRawData)) {
            return;
        }
        
        $crontabRawLines = explode("\n", $crontabRawData);
       
        foreach ($crontabRawLines as $crontabRawLine) {
            
            try {
                // Use The crontabJob Factory to test
                // if the line is a crontab job line
                $crontabJob = CrontabJob::createFromCrontabLine($crontabRawLine);
                $isCrontabJob = true;
            } catch (\Exception $e) {
                $isCrontabJob = false;
            }

            if ($isCrontabJob && !in_array($crontabRawLine, $this->crontabLinesToBypass)) {
                array_push($this->crontabJobs, $crontabJob);
            } else {
                // if any crontabjobs has been fund for now,
                // the line is a header comment
                if (empty($this->crontabJobs)) {
                    if (empty($this->headerComments)) {
                        $this->headerComments = $crontabRawLine . "\n";
                    } else {
                        $this->headerComments .= ($crontabRawLine . "\n");
                    }
                }
            }
        }
    }
}
