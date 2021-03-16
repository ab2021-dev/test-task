<?php

namespace App\Service\Builder;

use App\Value\Day;
use App\Value\Time;

interface  ScheduleBuilderInterface
{
    public function reset();
    public function createDays(Day $startDay, Day $endDay);
    public function getDaysCount(): int;
    public function createWorkDayTimeRanges(Time $workStart, Time $lunchStart);
    public function createSchedule();
    public function getSchedule(): array;
}
