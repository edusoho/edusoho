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

use TiBeN\CrontabManager\CrontabJob;

/**
 * CrontabJob class PHPUnit test cases
 *
 * @author TiBeN
 */
    
class CrontabJobTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test parsing crontab and creation of cronjob 
     * 
     * @dataProvider crontabLines
     */
    public function testCreateACrontabJobFromCrontabLine($crontabLine, $crontabJob)
    {
        $this->assertEquals($crontabJob, CrontabJob::createFromCrontabLine($crontabLine));
    }
    
    public static function crontabLines()
    {
        $crontabLines = array();
        
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(true)
            ->setMinutes(30)
            ->setHours(23)
            ->setDayOfMonth('*')
            ->setMonths('*')
            ->setDayOfWeek('*')
            ->setTaskCommandLine('df >> /tmp/df.log');
        $crontabLines[] = array('30 23 * * * df >> /tmp/df.log', $crontabJob);
        
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(true)
            ->setMinutes(12)
            ->setHours(10)
            ->setDayOfMonth('2-5')
            ->setMonths('*')
            ->setDayOfWeek('*')
            ->setTaskCommandLine('df >> /tmp/df.log');
        $crontabLines[] = array('12 10 2-5 * * df >> /tmp/df.log', $crontabJob);
        
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(true)
            ->setMinutes(59)
            ->setHours(23)
            ->setDayOfMonth('*/2')
            ->setMonths('*')
            ->setDayOfWeek('*')
            ->setTaskCommandLine('df >> /tmp/df.log');
        $crontabLines[] = array('59 23 */2 * * df >> /tmp/df.log', $crontabJob);
        
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(true)
            ->setMinutes(0)
            ->setHours(0)
            ->setDayOfMonth('25-31')
            ->setMonths('1,3,5,7,8,10,12')
            ->setDayOfWeek(0)
            ->setTaskCommandLine('my-script.sh');
        $crontabLines[] = array('0 0 25-31 1,3,5,7,8,10,12 0 my-script.sh', $crontabJob);
        
        /* space at start test case */
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(true)
            ->setMinutes(0)
            ->setHours('*')
            ->setDayOfMonth('*/2')
            ->setMonths('*')
            ->setDayOfWeek('*')
            ->setTaskCommandLine('my-script.sh')
            ->setComments('commentaire blablabla');
        $crontabLines[] = array(' 0 * */2 * * my-script.sh #commentaire blablabla', $crontabJob);

        /* Tab or spaces test case */
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(true)
            ->setMinutes(0)
            ->setHours('*')
            ->setDayOfMonth('*/2')
            ->setMonths('*')
            ->setDayOfWeek('*')
            ->setTaskCommandLine('my-script.sh')
            ->setComments('commentaire blablabla');
        $crontabLines[] = array('0  *    */2      *       *      my-script.sh #commentaire blablabla', $crontabJob);
        
        /* Cron shortcut test case */
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(true)
            ->setShortCut('daily')
            ->setTaskCommandLine('my-script.sh');
        $crontabLines[] = array('@daily my-script.sh', $crontabJob);
        
        /* Disabled cron test case - without spaces */
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(false)
            ->setMinutes(30)
            ->setHours(23)
            ->setDayOfMonth('*')
            ->setMonths('*')
            ->setDayOfWeek('*')
            ->setTaskCommandLine('df >> /tmp/df.log');
        $crontabLines[] = array('#30 23 * * * df >> /tmp/df.log', $crontabJob);

        /* Disabled cron test case - with spaces */
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(false)
            ->setMinutes(30)
            ->setHours(23)
            ->setDayOfMonth('*')
            ->setMonths('*')
            ->setDayOfWeek('*')
            ->setTaskCommandLine('df >> /tmp/df.log');
        $crontabLines[] = array('#   30 23 * * * df >> /tmp/df.log', $crontabJob);

        return $crontabLines;
    }
    
    public function testFormatCrontabLine()
    {
        /* Well formatted crontab line */
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setMinutes(30)
            ->setHours(23)
            ->setTaskCommandLine('df >> /tmp/df.log');
        $this->assertEquals('30 23 * * * df >> /tmp/df.log', $crontabJob->formatCrontabLine());
        $crontabJob = new CrontabJob();
        
        /* Crontab line formatted with a shortcut */
        $crontab = new CrontabJob();
        $crontabJob
            ->setShortCut('reboot')
            ->setTaskCommandLine('echo "youpi"');
        $this->assertEquals('@reboot echo "youpi"', $crontabJob->formatCrontabLine());
        
        /* crontab line with comments */
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setMinutes(30)
            ->setHours(23)
            ->setTaskCommandLine('df >> /tmp/df.log')
            ->setComments('This job is commented');
        $this->assertEquals(
            '30 23 * * * df >> /tmp/df.log #This job is commented', 
            $crontabJob->formatCrontabLine()
        );

        /* disabled crontab line */
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(false)
            ->setMinutes(30)
            ->setHours(23)
            ->setTaskCommandLine('df >> /tmp/df.log')
            ->setComments('This job is commented');
        $this->assertEquals(
            '#30 23 * * * df >> /tmp/df.log #This job is commented', 
            $crontabJob->formatCrontabLine()
        );

    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Crontab line not well formatted, so it can't be parsed
     */
    public function testCrontabLineNotWellFormattedNotEnoughStars()
    {
        CrontabJob::createFromCrontabLine('* * 15 * youpi');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Crontab line not well formatted, so it can't be parsed
     */
    public function testCrontabLineNotWellFormattedShortcutWithoutAtSign()
    {
        CrontabJob::createFromCrontabLine('daily youpi');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Crontab line not well formatted, so it can't be parsed
     */
    public function testCrontabLineNotWellFormattedUnknownShortcut()
    {
        CrontabJob::createFromCrontabLine('@maintenant youpi');
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage CrontabJob contains no task command line
     */
    public function testNoTaskSetException()
    {
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setMinutes(30)
            ->setHours(23)
            ->formatCrontabLine();
    }
    
    public function testSettersWithValidValues()
    {
        $crontabJob = new CrontabJob();
        $crontabJob
            ->setEnabled(true)
            ->setMinutes(50)
            ->setHours(20)
            ->setDayOfMonth(3)
            ->setMonths(4)
            ->setDayOfWeek('*')
            ->setTaskCommandLine('myScript.sh')
            ->setComments('My recurring task');
        $this->assertTrue($crontabJob->isEnabled());
        $this->assertEquals($crontabJob->getMinutes(), 50);
        $this->assertEquals($crontabJob->getHours(), 20);
        $this->assertEquals($crontabJob->getDayOfMonth(), 3);
        $this->assertEquals($crontabJob->getMonths(), 4);
        $this->assertEquals($crontabJob->getDayOfWeek(), '*');
        $this->assertEquals($crontabJob->getTaskCommandLine(), 'myScript.sh');
        $this->assertEquals($crontabJob->getComments(), 'My recurring task');
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The minutes value is not valid
     */
    public function testInvalidMinutes()
    {
        $crontabJob = new CrontabJob();
        $crontabJob->setMinutes(65);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The hours value is not valid
     */
    public function testInvalidHours()
    {
        $crontabJob = new CrontabJob();
        $crontabJob->setHours(27);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The day of month is not valid
     */
    public function testInvalidDayOfMonth()
    {
        $crontabJob = new CrontabJob();
        $crontabJob->setDayOfMonth(34);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The month value is not valid
     */
    public function testInvalidMonth()
    {
        $crontabJob = new CrontabJob();
        $crontabJob->setMonths(14);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The day of week is not valid
     */
    public function testInvalidDayOfWeek()
    {
        $crontabJob = new CrontabJob();
        $crontabJob->setDayOfWeek(9);
    }
}
