<?php

namespace App\Service\Factory;

use App\Value\TimeRange;
use App\Value\Time;

class TimeRangeFactory implements TimeRangeFactoryInterface
{
    private TimeFactoryInterface $timeFactory;

    public function __construct(TimeFactoryInterface $timeFactory)
    {
        $this->timeFactory = $timeFactory;
    }

    public function createFromStartEndTime(Time $start, Time $end): TimeRange
    {
        return new TimeRange($start, $end);
    }

    public function createByReduceEndTimeByOneHour(TimeRange $origin): TimeRange
    {
        $start = clone $origin->getStart();
        $end = $this->timeFactory->createBySubHoursFromTime($origin->getEnd(), 1);
        return $this->createFromStartEndTime($start, $end);
    }

    public function createByReduceStartTimeByOneHour(TimeRange $origin): TimeRange
    {
        $start = $this->timeFactory->createBySubHoursFromTime($origin->getStart(), 1);
        $end = clone $origin->getEnd();
        return $this->createFromStartEndTime($start, $end);
    }
}
