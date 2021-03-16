<?php

namespace App\Service\Validation;

use App\Service\Factory\TimeFactoryInterface;
use App\Value\Day;
use App\Value\Time;

class LogicValidation implements LogicValidationInterface
{
    private int $workHoursPerDay;
    private int $lunchHoursLength;
    private TimeFactoryInterface $timeFactory;
    
    public function __construct(int $workHoursPerDay, int $lunchHoursLength, TimeFactoryInterface $timeFactory)
    {
        $this->workHoursPerDay = $workHoursPerDay;
        $this->lunchHoursLength = $lunchHoursLength;
        $this->timeFactory = $timeFactory;
    }

    public function isDateIntervalCorrect(Day $startDate, Day $endDate): bool
    {
        if ($startDate->isGreaterThan($endDate)) {
            return false;
        }

        return true;
    }

    public function isWorkStartTimeCorrect(Time $workStart, Time $lunchStart): bool
    {
        if ($workStart->isGreaterThan($lunchStart)) {
            return false;
        }

        $endOfDay = $this->timeFactory->createDayEndTime();
        $workHoursWithLunch = $this->workHoursPerDay + $this->lunchHoursLength;
        $maxPossibleWorkStart = $this->timeFactory->createBySubHoursFromTime($endOfDay, $workHoursWithLunch);
        if ($workStart->isGreaterThan($maxPossibleWorkStart)) {
            return false;
        }

        return true;
    }
}
