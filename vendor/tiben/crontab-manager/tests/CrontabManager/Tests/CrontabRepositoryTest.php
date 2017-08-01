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

namespace TiBeN\CrontabManager\Tests;

use TiBeN\CrontabManager\CrontabRepository;
use TiBeN\CrontabManager\CrontabJob;
use TiBeN\CrontabManager\CrontabAdapter;

/**
 * CrontabRepository class PHPUnit test cases
 *
 * @author TiBeN
 */
class CrontabRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $fixturesPath;

    protected function setUp()
    {
        $this->fixturesPath = dirname(__FILE__) . '/Fixtures/';
    }

    /**
     * Test if the crontab file is parsed.
     */
    public function testParseCrontab()
    {
        /* Create fake crontabAdapter */
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');
        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will($this->returnValue(file_get_contents($this->fixturesPath . 'testing_read_crontab.txt')))
        ;

        /* Create expected crontabJobs */

        $expectedCrontabJob1 = new CrontabJob();
        $expectedCrontabJob1->minutes = '30';
        $expectedCrontabJob1->hours = '23';
        $expectedCrontabJob1->dayOfMonth = '*';
        $expectedCrontabJob1->months = '*';
        $expectedCrontabJob1->dayOfWeek = '*';
        $expectedCrontabJob1->taskCommandLine = 'df >> /tmp/df.log';
        $expectedCrontabJob1->comments = 'first crontabJob';

        $expectedCrontabJob2 = new CrontabJob();
        $expectedCrontabJob2->minutes = '0';
        $expectedCrontabJob2->hours = '0';
        $expectedCrontabJob2->dayOfMonth = '28-31';
        $expectedCrontabJob2->months = '*';
        $expectedCrontabJob2->dayOfWeek = '*';
        $expectedCrontabJob2->taskCommandLine
            = '[ `/bin/date +\%d` -gt `/bin/date +\%d -d "1 day"` ] && df >> /tmp/df.log'
        ;
        $expectedCrontabJob2->comments = 'second crontabJob';

        $expectedCrontabJob3 = new CrontabJob();
        $expectedCrontabJob3->shortCut = 'hourly';
        $expectedCrontabJob3->taskCommandLine 
            = 'df > /tmp/df_`date +\%d_\%m_\%Y_\%H_\%M`.log'
        ;
        $expectedCrontabJob3->comments = 'third crontabJob';

        $expectedCrontabJobs = array(
            $expectedCrontabJob1, 
            $expectedCrontabJob2, 
            $expectedCrontabJob3
        );

        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);
        $crontabJobs = $crontabRepository->getJobs();

        $this->assertEquals($expectedCrontabJobs, $crontabJobs);

    }

    /**
     * Test if the headers Comments of the crontab file are read
     */
    public function testReadHeaderComments()
    {
        /* Create fake crontabAdapter */
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');
        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixturesPath . 'simple_crontab.txt')
                )
            )
        ;

        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);

        $this->assertEquals(
            file_get_contents($this->fixturesPath . 'crontab_headers.txt'),
            $crontabRepository->headerComments
        );
    }

    /**
     * Test finding a job by a regular expression
     */
    public function testFindJobByRegex()
    {
        /* Create fake crontabAdapter */
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');
        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixturesPath . 'simple_crontab.txt')
                )
            )
        ;

        $crontabJob = new CrontabJob();
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->dayOfMonth = '*';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'launch -param mycommand';

        $expectedCrontabJobs = array($crontabJob);

        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);
        $crontabJobs = $crontabRepository->findJobByRegex('/launch -param mycommand/');

        $this->assertEquals($expectedCrontabJobs, $crontabJobs);

    }

    /**
     * Test finding a job by a regular expression
     * using the comments
     */
    public function testFindJobByRegexUsingComments()
    {
        /* Create fake crontabAdapter */
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');
        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will(
                $this->returnValue("")
            )
        ;

        $crontabJob = new CrontabJob();
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->dayOfMonth = '*';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $crontabJob->comments = 'Logging disk usage';

        $expectedCrontabJobs = array($crontabJob);

        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);
        $crontabRepository->addJob($crontabJob);

        $crontabJobs = $crontabRepository->findJobByRegex('/Logging\ disk\ usage/'); 

        $this->assertEquals($expectedCrontabJobs, $crontabJobs);
    }

    /**
     * This test will modify an existing job and append a new job to the crontab.
     */
    public function testPersist()
    {
        /* Create fake crontabAdapter */
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');

        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixturesPath . 'simple_crontab.txt')
                )
            )
        ;

        $fakeCrontabAdapter
            ->expects($this->once())
            ->method('writeCrontab')
            ->with(
                $this->equalTo(
                    file_get_contents(
                        $this->fixturesPath . 'testing_persisted_crontab.txt'
                    )
                )
            )
        ;

        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);

        /* Modify the existing job */
        $crontabJobs = $crontabRepository->findJobByRegex('/launch\ -param\ mycommand/');
        $crontabJobs[0]->minutes = '01';
        $crontabJobs[0]->hours = '05';

        /* Append new job */
        $newCrontabJob = new CrontabJob();
        $newCrontabJob->minutes = '30';
        $newCrontabJob->hours = '23';
        $newCrontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $newCrontabJob->comments = 'new crontab job';

        $crontabRepository->addJob($newCrontabJob);

        $crontabRepository->persist();
    }

    /**
     * Test Remove a job
     */
    public function testRemove()
    {
        /* Create fake crontabAdapter */
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');

        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixturesPath . 'testing_persisted_crontab.txt')
                )
            )
        ;

        $fakeCrontabAdapter
            ->expects($this->once())
            ->method('writeCrontab')
            ->with(
                $this->equalTo(
                    file_get_contents($this->fixturesPath . 'testing_removed_crontab.txt')
                )
            )
        ;

        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);

        /* Retrieve job */
        $crontabJobs = $crontabRepository->findJobByRegex('/launch\ -param\ mycommand/');

        /* Apply some local updates on the job to ensure
         * it is linked by reference to the repository
         */
        $crontabJobs[0]->minutes = '00';
        $crontabJobs[0]->hours = '01';

        /* Remove job */
        $crontabRepository->removeJob($crontabJobs[0]);
        $crontabRepository->persist();
    }

    /**
     * Test if pass a wrong regular expression when searching 
     * by regex throw an invalid regex exception
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Not a valid Regex : preg_match(): No ending delimiter '/' found
     */
    public function testExceptionInvalidRegexOnFindJobByRegex()
    {
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');

        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixturesPath . 'simple_crontab.txt')
                )
            )
        ;
        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);
        $crontabRepository->findJobByRegex('/$');
    }

    /**
     * Test remove an unknown Job
     *
     * @expectedException LogicException
     * @expectedExceptionMessage This job is not part of this crontab
     */
    public function testRemoveAnUnknownJob()
    {
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');

        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixturesPath . 'simple_crontab.txt')
                )
            )
        ;
        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);

        $job = CrontabJob::createFromCrontabLine('30 23 * * * launch -param mycommand');
        $crontabRepository->removeJob($job);
    }

    /**
     * Add an already in the repository job
     *
     * @expectedException \LogicException
     */
    public function testAddAnAlreadyInTheRepositoryJob()
    {
        $fakeCrontabAdapter = $this->getMock('TiBeN\CrontabManager\CrontabAdapter');

        $fakeCrontabAdapter
            ->expects($this->any())
            ->method('readCrontab')
            ->will(
                $this->returnValue(
                    file_get_contents($this->fixturesPath . 'simple_crontab.txt')
                )
            )
        ;

        $crontabRepository = new CrontabRepository($fakeCrontabAdapter);

        /* Modify the existing job */
        $crontabJobs = $crontabRepository->findJobByRegex('/launch\ -param\ mycommand/');
        $crontabRepository->addJob($crontabJobs[0]);
    }
}
