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
 * CrontabJob
 * Represents a Job of the crontab.
 * 
 * @author TiBeN
 */
class CrontabJob
{
    /**
     * Tells whether the cron job is enabled or not
     * This will add or not a # at the beginning of the cron line
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * Min (0 - 59)
     *
     * @var String/int
     *
     */
    public $minutes;
    
    /**
     * Hour (0 - 23)
     *
     * @var String/int
     */
    public $hours;
    
    /**
     * Day of month (1 - 31)
     *
     * @var String/int
     */
    public $dayOfMonth;
    
    /**
     * Month (1 - 12)
     *
     * @var String/int
     */
    public $months;
    
    /**
     * Day of week (0 - 6) (0 or 6 are Sunday to Saturday, or use names)
     *
     * @var String/int
     */
    public $dayOfWeek;

    /**
     * The task command line to be executed 
     *
     * @var String
     */
    public $taskCommandLine;
    
    /**
     * Optional comment that will be placed at the end of the crontab line 
     * and preceded by a #
     *
     * @var String
     */
    public $comments;
    
    /**
     * Predefined scheduling definition
     * Shorcut definition that replaces the standard definition (preceded by @)
     * possible values : yearly, monthly, weekly, daily, hourly, reboot
     * When a shortcut is defined, it overwrites the stantard definition
     *
     * @var String
     */
    public $shortCut;
    
    /* Getters and Setters */
    
    /**
     * Gets the enabled status of the cron job
     * @return Boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * Sets the enabled status of the cron job
     * @param Boolean $enabled
     * @return $this
     */
    public function setEnabled($enabled = true) 
    {
        $this->enabled = $enabled;
        return $this;
    }
    
    /**
     * Gets the number of minutes.
     * @return Int
     */
    public function getMinutes()
    {
        return $this->minutes;
    }
    
    /**
     * Sets the number of minutes.
     * @param Int/String $minutes
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setMinutes($minutes)
    {
        if (is_numeric($minutes) && ((int)$minutes < 0 || (int)$minutes > 59))
        {
            throw new \InvalidArgumentException(
                'The minutes value is not valid'
            );
        }
        
        $this->minutes = $minutes;
        return $this;
    }
    
    /**
     * Gets the number of hours.
     * @return Int
     */
    public function getHours()
    {
        return $this->hours;
    }
    
    /**
     * Sets the number of hours.
     * @param Int/String $hours
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setHours($hours)
    {
        if (is_numeric($hours) && ((int)$hours < 0 || (int)$hours > 23))
        {
            throw new \InvalidArgumentException(
                'The hours value is not valid'
            );
        }
        
        $this->hours = $hours;
        return $this;
    }
    
    /**
     * Gets the day of month.
     * @return Int
     */
    public function getDayOfMonth()
    {
        return $this->dayOfMonth;
    }
    
    /**
     * Sets the day of month.
     * @param Int/String $dayOfMonth
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setDayOfMonth($dayOfMonth)
    {
        if (is_numeric($dayOfMonth) && ((int)$dayOfMonth < 1 || (int)$dayOfMonth > 31))
        {
            throw new \InvalidArgumentException(
                'The day of month is not valid'
            );
        }
        
        $this->dayOfMonth = $dayOfMonth;
        return $this;
    }
    
        /**
     * Gets the month number.
     * @return Int
     */
    public function getMonths()
    {
        return $this->months;
    }
    
    /**
     * Sets the month number.
     * @param Int/String $months
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setMonths($months)
    {
        if (is_numeric($months) && ((int)$months < 1 || (int)$months > 12))
        {
            throw new \InvalidArgumentException(
                'The month value is not valid'
            );
        }
        
        $this->months = $months;
        return $this;
    }
    
        /**
     * Gets the day of week.
     * @return Int
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }
    
    /**
     * Sets the day of week.
     * @param Int/String $dayOfWeek
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setDayOfWeek($dayOfWeek)
    {
        // 0 and 7 are both valid for Sunday
        if (is_numeric($dayOfWeek) && ((int)$dayOfWeek < 0 || (int)$dayOfWeek > 7))
        {
            throw new \InvalidArgumentException(
                'The day of week is not valid'
            );
        }
        
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }
    
    /**
     * Gets the task command line.
     * @return String
     */
    public function getTaskCommandLine()
    {
        return $this->taskCommandLine;
    }
    
    /**
     * Sets the task command line.
     * @param String $taskCommandLine
     * @return $this
     */
    public function setTaskCommandLine($taskCommandLine)
    {
        $this->taskCommandLine = $taskCommandLine;
        return $this;
    }
    
    /**
     * Gets the job comments.
     * @return String
     */
    public function getComments()
    {
        return $this->comments;
    }
    
    /**
     * Sets the optional job comments.
     * @param String $comments
     * @return $this
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }
    
    /**
     * Gets the time shortcut.
     * @return String
     */
    public function getShortCut()
    {
        return $this->shortCut;
    }
    
    /**
     * Sets the time shortcut.
     * @param String $shortCut
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setShortCut($shortCut)
    {
        // @annually is the same as @yearly, and @midnight is the same as @daily
        $possibleValues = array(
            'yearly', 'annually', 'monthly', 'weekly', 'daily', 'midnight', 'hourly', 'reboot'
        );
        if (!in_array($shortCut, $possibleValues)) {
            throw new \InvalidArgumentException(
                'The shortcut is not valid.'
            );
        }
        
        $this->shortCut = $shortCut;
        return $this;
    }

    /**
     * Factory method to create a CrontabJob from a crontab line.
     *
     * @param String $crontabLine
     * @throws InvalidArgumentException
     * @return CrontabJob
     */
    public static function createFromCrontabLine($crontabLine)
    {
        // Check crontab line format validity
        $crontabLineRegex = '/^[\s\t]*(#)?[\s\t]*(([*0-9,-\/]+)[\s\t]+([*0-9,-\/]+)'
            . '[\s\t]+([*0-9,-\/]+)[\s\t]+([*a-z0-9,-\/]+)[\s\t]+([*a-z0-9,-\/]+)|'
            . '(@(reboot|yearly|annually|monthly|weekly|daily|midnight|hourly)))'
            . '[\s\t]+([^#]+)([\s\t]+#(.+))?$/'
        ;

        if (!preg_match($crontabLineRegex, $crontabLine, $matches)) {
            throw new \InvalidArgumentException(
                'Crontab line not well formatted, so it can\'t be parsed'
            );
        }

        // Create the job from parsed crontab line values
        $crontabJob = new self();
      
        if (!empty($matches[1])) {
            $crontabJob->setEnabled(false);
        }

        if (!empty($matches)) {
            $crontabJob
                ->setMinutes($matches[3])
                ->setHours($matches[4])
                ->setDayOfMonth($matches[5])
                ->setMonths($matches[6])
                ->setDayOfWeek($matches[7]);
        }
        
        if (!empty($matches[8])) {
            $crontabJob->setShortCut($matches[9]);
        }
        
        $crontabJob->setTaskCommandLine($matches[10]);
        if (!empty($matches[12])) {
            $crontabJob->setComments($matches[12]);
        }
        
        return $crontabJob;
    }
    
    /**
     * Format the CrontabJob to a crontab line 
     *
     * @throws InvalidArgumentException
     * @return String
     */
    public function formatCrontabLine()
    {
        
        // Check if job has a task command line
        if (!isset($this->taskCommandLine) || empty($this->taskCommandLine)) {
            throw new \InvalidArgumentException(
                'CrontabJob contains no task command line'
            );
        }
        
        $taskPlanningNotation = (isset($this->shortCut) && !empty($this->shortCut))
            ? sprintf('@%s', $this->shortCut)
            : sprintf(
                '%s %s %s %s %s',
                (isset($this->minutes) ? $this->minutes : '*'),
                (isset($this->hours) ? $this->hours : '*'),
                (isset($this->dayOfMonth) ? $this->dayOfMonth : '*'),
                (isset($this->months) ? $this->months : '*'),
                (isset($this->dayOfWeek) ? $this->dayOfWeek : '*')
            )
        ;
        
        return sprintf(
            '%s%s %s%s',
            ($this->enabled ? '' : '#'),
            $taskPlanningNotation,
            $this->taskCommandLine,
            (isset($this->comments) ? (' #' . $this->comments) : '')
        );
    }
}
