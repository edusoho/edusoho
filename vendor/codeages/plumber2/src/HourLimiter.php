<?php

namespace Codeages\Plumber;

class HourLimiter
{
    /**
     * Job开始可执行的时间点（小时）
     *
     * @var integer
     */
    private $start;

    /**
     * Job最后可执行的时间点(小时）
     * @var integer
     */
    private $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function isLimited($timestamp = null)
    {
        if ($this->start > $this->end) {
            $hours = range($this->start, 23);
            $hours = array_merge($hours, range(0, $this->end-1));
        } else if ($this->start < $this->end) {
            $hours = range($this->start, $this->end - 1);
        } else {
            $hours = [];
        }

        if (empty($timestamp)) {
            $current = (int)date('G');
        } else {
            $current = (int)date('G', $timestamp);
        }

        var_dump($current, $hours);
        var_dump(date("Y-m-d H:i:s"));

        return ! in_array($current, $hours);
    }
}