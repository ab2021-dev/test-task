<?php

namespace App\Service\Factory;

use App\Entity\Employee;
use stdClass;

interface EmployeeFactoryInterface
{
    public function createFromJson(stdClass $item): ?Employee;
}
