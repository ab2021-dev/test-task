<?php

namespace App\Service\Builder;

use App\Service\Factory\DayFactoryInterface;
use App\Service\Factory\TimeFactoryInterface;
use App\Service\Factory\TimeRangeFactoryInterface;
use App\Value\Day;
use App\Value\Time;
use App\Service\ExternalApi\CalendarInterface;

abstract class AbstractScheduleBuilder implements ScheduleBuilderInterface
{
    private int $workHoursPerDay;
    private int $lunchHoursLength;
    protected array $schedule;
    protected array $days;
    protected array $workDayTimeRanges;
    private CalendarInterface $calendar;
    protected DayFactoryInterface $dayFactory;
    protected TimeFactoryInterface $timeFactory;
    protected TimeRangeFactoryInterface $timeRangeFactory;

    public function __construct(
        int $workHoursPerDay,
        int $lunchHoursLength,
        CalendarInterface $calendar,
        DayFactoryInterface $dayFactory,
        TimeFactoryInterface $timeFactory,
        TimeRangeFactoryInterface $timeRangeFactory
    )
    {
        $this->workHoursPerDay = $workHoursPerDay;
        $this->lunchHoursLength = $lunchHoursLength;
        $this->calendar = $calendar;
        $this->dayFactory = $dayFactory;
        $this->timeFactory = $timeFactory;
        $this->timeRangeFactory = $timeRangeFactory;

        $this->reset();
    }

    public function reset()
    {
        $this->schedule = [];
        $this->days = [];
        $this->workDayTimeRanges = [];
    }

    public function getSchedule(): array
    {
        return $this->schedule;
    }

    protected function isDayOff(Day $day): bool
    {
        return $this->calendar->isDayOff($day);
    }

    protected function isDayWorkingButShort(Day $day): bool
    {
        return $this->calendar->isDayWorkingButShort($day);
    }

    public function getDaysCount(): int
    {
        return count($this->days);
    }

    public function getLunchEnd(Time $lunchStart): Time
    {
        return $this->timeFactory->createByAddHoursToTime($lunchStart, $this->lunchHoursLength);
    }

    public function getWorkEnd(Time $workStart, Time $lunchStart, Time $lunchEnd): Time
    {
        $workHoursAfterLunch = $this->getWorkHoursAfterLunch($workStart, $lunchStart);
        return $this->timeFactory->createByAddHoursToTime($lunchEnd, $workHoursAfterLunch);
    }

    private function getWorkHoursAfterLunch(Time $workStart, Time $lunchStart): int
    {
        $workHoursBeforeLunch = $workStart->getHoursDiffTo($lunchStart);
        return ($this->workHoursPerDay - $workHoursBeforeLunch);
    }

    abstract public function createDays(Day $startDay, Day $endDay);
    abstract public function createWorkDayTimeRanges(Time $workStart, Time $lunchStart);
    abstract public function createSchedule();
}
