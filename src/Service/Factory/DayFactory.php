<?php

namespace App\Service\Factory;

use App\Value\Day;
use DateInterval;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Exception;

class DayFactory implements DayFactoryInterface
{
    public function createFromString(string $date): Day
    {
        try {
            $day = new Day($date);
            return $day;
        } catch (Exception $e) {
            throw new BadRequestHttpException("Date format is not correct.");
        }
    }

    public function createNextDayAfter(Day $day): Day
    {
        $nextDateTime = (clone $day->getDateTime())->add(new DateInterval('P1D'));
        return $this->createFromString($nextDateTime->format('Y-m-d'));
    }
}
