<?php

namespace App\Service\Builder;

use App\Value\Time;
use App\Value\Day;
use App\Value\DaySchedule;

class NonWorkScheduleBuilder extends AbstractScheduleBuilder implements NonWorkScheduleBuilderInterface
{
    public function createDays(Day $startDay, Day $endDay)
    {
        $currentDay = $startDay;
        do {
            $this->days[] = $currentDay;
            $currentDay = $this->dayFactory->createNextDayAfter($currentDay);
        } while ($endDay->isGreaterOrEqualThan($currentDay));
    }

    public function createWorkDayTimeRanges(Time $workStart, Time $lunchStart)
    {
        $timeRangeBeforeWork = $this->timeRangeFactory->createFromStartEndTime(
            $this->timeFactory->createDayStartTime(),
            $workStart
        );
        $this->workDayTimeRanges[] = $timeRangeBeforeWork;

        $lunchEnd = $this->getLunchEnd($lunchStart);
        $timeRangeForLunch = $this->timeRangeFactory->createFromStartEndTime($lunchStart, $lunchEnd);
        $this->workDayTimeRanges[] = $timeRangeForLunch;

        $workEnd = $this->getWorkEnd($workStart, $lunchStart, $lunchEnd);
        $timeRangeAfterWork = $this->timeRangeFactory->createFromStartEndTime(
            $workEnd,
            $this->timeFactory->createDayEndTime()
        );
        $this->workDayTimeRanges[] = $timeRangeAfterWork;
    }

    public function createSchedule()
    {
        foreach ($this->days as $day) {
            if ($this->isDayOff($day)) {
                $timeRanges = $this->createOffDayTimeRanges();
            } else if ($this->isDayWorkingButShort($day)) {
                $timeRanges = $this->createShortWorkDayTimeRanges();
            } else {
                $timeRanges = $this->workDayTimeRanges;
            }

            $this->schedule[] = new DaySchedule($day, $timeRanges);
        }
    }

    private function createOffDayTimeRanges(): array
    {
        $offDayTimeRange = $this->timeRangeFactory->createFromStartEndTime(
            $this->timeFactory->createDayStartTime(),
            $this->timeFactory->createDayEndTime()
        );
        $timeRanges = [];
        $timeRanges[] = $offDayTimeRange;
        return $timeRanges;
    }

    private function createShortWorkDayTimeRanges(): array
    {
        return $this->reduceStartTimeOfLastRange($this->workDayTimeRanges);
    }

    private function reduceStartTimeOfLastRange(array $timeRanges): array
    {
        $lastRange = array_pop($timeRanges);
        if (!is_null($lastRange)) {
            $timeRanges[] = $this->timeRangeFactory->createByReduceStartTimeByOneHour($lastRange);
        }
        return $timeRanges;
    }
}
