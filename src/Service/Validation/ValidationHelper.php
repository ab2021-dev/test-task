<?php

namespace App\Service\Validation;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationHelper implements ValidationHelperInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateScheduleRequestParams(string $startDate, string $endDate, string $employeeId): ConstraintViolationListInterface
    {
        $inputData = [
            "startDate" => $startDate,
            "endDate" => $endDate,
            "employeeId" => $employeeId
        ];

        $constraint = new Assert\Collection([
            'startDate' => [
                new Assert\NotBlank(['message' => 'Start Date should not be blank.']),
                new Assert\Date(['message' => 'Start Date should be a correct date.'])
            ],
            'endDate' => [
                new Assert\NotBlank(['message' => 'End Date should not be blank.']),
                new Assert\Date(['message' => 'End Date should be a correct date.'])
            ],
            'employeeId' => [
                new Assert\NotBlank(['message' => 'Employee Id should not be blank.']),
                new Assert\Positive(['message' => 'Employee Id should be correct number greater than 0..'])
            ]
        ]);

        return $this->validator->validate($inputData, $constraint);
    }
}
