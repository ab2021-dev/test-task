<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Exception\UnableToLoadEmployeeDataException;
use App\Service\Factory\EmployeeFactoryInterface;
use Exception;
use TypeError;

class EmployeeRepository
{
    private array $employees;
    private EmployeeFactoryInterface $employeeFactory;

    public function __construct(EmployeeFactoryInterface $employeeFactory, string $dataFilePath)
    {
        $this->employeeFactory = $employeeFactory;
        $this->loadEmployeesFromFile($dataFilePath);
    }

    private function loadEmployeesFromFile(string $path)
    {
        $this->employees = [];

        try {
            if (!file_exists($path)) {
                throw new Exception('File "'.$path.' is not exists."');
            }

            $contents = file_get_contents($path);
            if ($contents === false) {
                throw new Exception('Unable to read file "'.$path.'."');
            }

            $data = json_decode($contents);
            if (is_null($data)) {
                throw new Exception('Unable to decode file "'.$path.'."');
            }

            $this->employees = $data;
        } catch (Exception | TypeError $e) {
            throw new UnableToLoadEmployeeDataException($e->getMessage());
        }
    }

    public function findById(string $value): ?Employee
    {
        foreach ($this->employees as $item) {
            if (isset($item->id) && $item->id === $value) {
                return $this->employeeFactory->createFromJson($item);
            }
        }

        return null;
    }
}
