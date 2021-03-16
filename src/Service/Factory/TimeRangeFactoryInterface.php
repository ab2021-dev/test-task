<?php

namespace App\Service\Factory;

use App\Value\TimeRange;
use App\Value\Time;

interface TimeRangeFactoryInterface
{
    public function createFromStartEndTime(Time $start, Time $end): TimeRange;
    public function createByReduceEndTimeByOneHour(TimeRange $origin): TimeRange;
    public function createByReduceStartTimeByOneHour(TimeRange $origin): TimeRange;
}
