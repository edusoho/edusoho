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
    
class CrontabJobTest extends \PHPUnit_Framework_TestCase
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
        $crontabJob->enabled = true;
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->dayOfMonth = '*';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $crontabLines[] = array('30 23 * * * df >> /tmp/df.log', $crontabJob);
        
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = true;
        $crontabJob->minutes = '12';
        $crontabJob->hours = '10';
        $crontabJob->dayOfMonth = '2-5';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $crontabLines[] = array('12 10 2-5 * * df >> /tmp/df.log', $crontabJob);
        
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = true;
        $crontabJob->minutes = '59';
        $crontabJob->hours = '23';
        $crontabJob->dayOfMonth = '*/2';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $crontabLines[] = array('59 23 */2 * * df >> /tmp/df.log', $crontabJob);
        
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = true;
        $crontabJob->minutes = '0';
        $crontabJob->hours = '0';
        $crontabJob->dayOfMonth = '25-31';
        $crontabJob->months = '1,3,5,7,8,10,12';
        $crontabJob->dayOfWeek = '0';
        $crontabJob->taskCommandLine = 'my-script.sh';
        $crontabLines[] = array('0 0 25-31 1,3,5,7,8,10,12 0 my-script.sh', $crontabJob);
        
        /* space at start test case */
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = true;
        $crontabJob->minutes = '0';
        $crontabJob->hours = '*';
        $crontabJob->dayOfMonth = '*/2';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'my-script.sh';
        $crontabJob->comments = 'commentaire blablabla';
        $crontabLines[] = array(' 0 * */2 * * my-script.sh #commentaire blablabla', $crontabJob);

        /* Tab or spaces test case */
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = true;
        $crontabJob->minutes = '0';
        $crontabJob->hours = '*';
        $crontabJob->dayOfMonth = '*/2';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'my-script.sh';
        $crontabJob->comments = 'commentaire blablabla';
        $crontabLines[] = array('0  *    */2      *       *      my-script.sh #commentaire blablabla', $crontabJob);
        
        /* Cron shortcut test case */
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = true;
        $crontabJob->shortCut = 'daily';
        $crontabJob->taskCommandLine = 'my-script.sh';
        $crontabLines[] = array('@daily my-script.sh', $crontabJob);
        
        /* Disabled cron test case - without spaces */
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = false;
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->dayOfMonth = '*';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $crontabLines[] = array('#30 23 * * * df >> /tmp/df.log', $crontabJob);

        /* Disabled cron test case - with spaces */
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = false;
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->dayOfMonth = '*';
        $crontabJob->months = '*';
        $crontabJob->dayOfWeek = '*';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $crontabLines[] = array('#   30 23 * * * df >> /tmp/df.log', $crontabJob);

        return $crontabLines;
    }
    
    public function testFormatCrontabLine()
    {
        /* Normal formated crontab line */
        $crontabJob = new CrontabJob();
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $this->assertEquals('30 23 * * * df >> /tmp/df.log', $crontabJob->formatCrontabLine());
        $crontabJob = new CrontabJob();
        
        /* Shortcut formated crontab line */
        $crontab = new CrontabJob();
        $crontabJob->shortCut = 'reboot';
        $crontabJob->taskCommandLine = 'echo "youpi"';
        $this->assertEquals('@reboot echo "youpi"', $crontabJob->formatCrontabLine());
        
        /* crontab line with comments */
        $crontabJob = new CrontabJob();
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $crontabJob->comments = 'This job is commented';
        $this->assertEquals(
            '30 23 * * * df >> /tmp/df.log #This job is commented', 
            $crontabJob->formatCrontabLine()
        );

        /* disabled crontab line */
        $crontabJob = new CrontabJob();
        $crontabJob->enabled = false;
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->taskCommandLine = 'df >> /tmp/df.log';
        $crontabJob->comments = 'This job is commented';
        $this->assertEquals(
            '#30 23 * * * df >> /tmp/df.log #This job is commented', 
            $crontabJob->formatCrontabLine()
        );

    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Crontab line not well formated then can't be parsed
     */
    public function testCrontabLineNotWellFormatedNotEnoughStars()
    {
        CrontabJob::createFromCrontabLine('* * 15 * youpi');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Crontab line not well formated then can't be parsed
     */
    public function testCrontabLineNotWellFormatedShortcutWithoutArobase()
    {
        CrontabJob::createFromCrontabLine('daily youpi');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Crontab line not well formated then can't be parsed
     */
    public function testCrontabLineNotWellFormatedUnknownShortcut()
    {
        CrontabJob::createFromCrontabLine('@maintenant youpi');
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage CrontabJob contain's no task command line
     */
    public function testNoTaskSetException()
    {
        $crontabJob = new CrontabJob();
        $crontabJob->minutes = '30';
        $crontabJob->hours = '23';
        $crontabJob->formatCrontabLine();
    }
}
