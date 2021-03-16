<?php

namespace App\Service\Builder;

use App\Value\Day;
use App\Value\Time;
use App\Value\DaySchedule;

class WorkScheduleBuilder extends AbstractScheduleBuilder implements WorkScheduleBuilderInterface
{
    public function createDays(Day $startDay, Day $endDay)
    {
        $currentDay = $startDay;
        do {
            if (!$this->isDayOff($currentDay)) {
                $this->days[] = $currentDay;
            }
            $currentDay = $this->dayFactory->createNextDayAfter($currentDay);
        } while ($endDay->isGreaterOrEqualThan($currentDay));
    }

    public function createWorkDayTimeRanges(Time $workStart, Time $lunchStart)
    {
        $timeRangeBeforeLunch = $this->timeRangeFactory->createFromStartEndTime($workStart, $lunchStart);
        $this->workDayTimeRanges[] = $timeRangeBeforeLunch;

        $lunchEnd = $this->getLunchEnd($lunchStart);
        $workEnd = $this->getWorkEnd($workStart, $lunchStart, $lunchEnd);
        $timeRangeAfterLunch = $this->timeRangeFactory->createFromStartEndTime($lunchEnd, $workEnd);
        $this->workDayTimeRanges[] = $timeRangeAfterLunch;
    }

    public function createSchedule()
    {
        foreach ($this->days as $day) {
            if ($this->isDayWorkingButShort($day)) {
                $timeRanges = $this->createShortWorkDayTimeRanges();
            } else {
                $timeRanges = $this->workDayTimeRanges;
            }
            $this->schedule[] = new DaySchedule($day, $timeRanges);
        }
    }

    private function createShortWorkDayTimeRanges(): array
    {
        return $this->reduceEndTimeOfLastRange();
    }

    private function reduceEndTimeOfLastRange(): array
    {
        $timeRanges = $this->workDayTimeRanges;
        $lastRange = array_pop($timeRanges);
        if (!is_null($lastRange)) {
            $timeRanges[] = $this->timeRangeFactory->createByReduceEndTimeByOneHour($lastRange);
        }
        return $timeRanges;
    }
}
