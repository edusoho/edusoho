<?php

namespace Biz\Classroom;

class DateTimeRange
{
    protected $startDate;

    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getStartTime()
    {
        return strtotime($this->startDate);
    }

    public function getEndTime()
    {
        return strtotime($this->endDate);
    }

    public function getStartDateTime()
    {
        return new \DateTime($this->startDate);
    }

    public function getEndDateTime()
    {
        return new \DateTime($this->endDate);
    }
}
