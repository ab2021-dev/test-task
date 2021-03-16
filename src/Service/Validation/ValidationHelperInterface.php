<?php

namespace App\Service\Validation;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ValidationHelperInterface
{
    public function validateScheduleRequestParams(string $startDate, string $endDate, string $employeeId): ConstraintViolationListInterface;
}
