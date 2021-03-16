<?php

namespace App\Value;

class DaySchedule
{
    private Day $day;
    private array $timeRanges;

    public function __construct(Day $day, array $timeRanges)
    {
        $this->day = $day;
        $this->timeRanges = $timeRanges;
    }

    public function getDay(): Day
    {
        return $this->day;
    }

    public function getTimeRanges(): array
    {
        return $this->timeRanges;
    }
}
