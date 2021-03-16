<?php

namespace App\Service\Factory;

use App\Entity\Employee;
use App\Service\Validation\LogicValidation;
use App\Value\EmployeeJsonItem;
use stdClass;
use Exception;

class EmployeeFactory implements EmployeeFactoryInterface
{
    private TimeFactoryInterface $timeFactory;
    private LogicValidation $logicValidation;

    public function __construct(TimeFactoryInterface $timeFactory, LogicValidation $logicValidation)
    {
        $this->timeFactory = $timeFactory;
        $this->logicValidation = $logicValidation;
    }

    public function createFromJson(stdClass $item): ?Employee
    {
        try {
            $workStart = $this->timeFactory->createFromString($item->workStart);
            $lunchStart = $this->timeFactory->createFromString($item->lunchStart);
        } catch (Exception $e) {
            return null;
        }

        if ($this->logicValidation->isWorkStartTimeCorrect($workStart, $lunchStart)) {
            return new Employee($item->id, $workStart, $lunchStart);
        }

        return null;
    }
}
