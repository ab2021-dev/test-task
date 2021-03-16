<?php

namespace App\Service;

use App\Service\Builder\ScheduleBuilderInterface;

interface ScheduleDirectorInterface
{
    public function setBuilder(ScheduleBuilderInterface $employeeScheduleBuilder);
    public function createSchedule(string $startDate, string $endDate, string $employeeId);
}
