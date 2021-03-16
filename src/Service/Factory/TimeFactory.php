<?php

namespace App\Service\Factory;

use App\Value\Time;
use DateInterval;
use DateTime;
use Exception;
use http\Exception\BadMethodCallException;

class TimeFactory implements TimeFactoryInterface
{
    public const DAY_START_TIME = "00:00";
    public const DAY_END_TIME = "24:00";

    public function createFromString(string $time): Time
    {
        try {
            $time = new Time($time);
            return $time;
        } catch (Exception $e) {
            throw new BadMethodCallException("Time format is not correct.");
        }
    }

    public function createByAddHoursToTime(Time $origin, int $numberOfHours): Time
    {
        $nextTime = $this->cloneDateTime($origin)->add(new DateInterval('PT'.$numberOfHours.'H'));
        return $this->createFromString($nextTime->format('H:i'));
    }

    private function cloneDateTime(Time $origin): DateTime
    {
        return (clone $origin->getDateTime());
    }

    public function createBySubHoursFromTime(Time $origin, int $numberOfHours): Time
    {
        $previousTime = $this->cloneDateTime($origin)->sub(new DateInterval('PT'.$numberOfHours.'H'));
        return $this->createFromString($previousTime->format('H:i'));
    }

    public function createDayStartTime(): Time
    {
        return $this->createFromString(self::DAY_START_TIME);
    }

    public function createDayEndTime(): Time
    {
        return $this->createFromString(self::DAY_END_TIME);
    }
}
