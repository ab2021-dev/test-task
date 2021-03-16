<?php

namespace App\Tests\Controller;

use Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class BaseEmployeeScheduleControllerTest extends WebTestCase
{
    public const CALENDAR_SERVICE_IS_NOT_AVAILABLE_MESSAGE = 'Calendar service is not available.';
    public const CALENDAR_SERVICE_ID = 'App\Service\ExternalApi\Calendar';
    public const SHORT_WORK_DAY_DATE = '2021-02-20';
    public const WEEKEND_DATE = '2021-02-21';
    public const HOLIDAY_DATE = '2021-02-23';
    public const WORK_DAY_DATE = '2021-02-24';
    public const NEXT_WORK_DAY_DATE = '2021-02-25';
    public const WEEKEND_START_DATE = '2021-02-27';
    public const WEEKEND_END_DATE = '2021-02-28';
    public const WRONG_EMPLOYEE_ID = 3;
    public const ORIGINAL_EMPLOYEES_FILE_PERMISSIONS = 0644;
    public const EMPTY_EMPLOYEES_FILE_PERMISSIONS = 0000;
    protected $originalEmployeesFilePath;
    protected $backupEmployeesFilePath;
    protected $renamedEmployeesFilePath;
    protected $isChangedEmployeesFilePermissions;
    protected $isChangedEmployeesFileContent;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $dataFilePath = $this->client->getContainer()->getParameter('employees_data_file_path');
        $this->originalEmployeesFilePath = $dataFilePath;
        $this->renamedEmployeesFilePath = null;
        $this->isChangedEmployeesFilePermissions = false;
        $this->isChangedEmployeesFileContent = false;
        $this->backupEmployeesFilePath = $dataFilePath . '_backup';
        copy($dataFilePath, $this->backupEmployeesFilePath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (!is_null($this->renamedEmployeesFilePath)) {
            rename($this->renamedEmployeesFilePath, $this->originalEmployeesFilePath);
            $this->renamedEmployeesFilePath = null;
        }

        if ($this->isChangedEmployeesFilePermissions) {
            chmod($this->originalEmployeesFilePath, self::ORIGINAL_EMPLOYEES_FILE_PERMISSIONS);
            $this->isChangedEmployeesFilePermissions = false;
        }

        if ($this->isChangedEmployeesFileContent) {
            unlink($this->originalEmployeesFilePath);
            rename($this->backupEmployeesFilePath, $this->originalEmployeesFilePath);
            $this->isChangedEmployeesFileContent = false;
        }
    }

    public function getEmployeesData(): Generator
    {
        yield 'Employee who works from the late morning' =>  [
            'id' => '1',
            'workDaySchedule' => [
                [
                    'start' => '10:00',
                    'end' => '13:00',
                ],
                [
                    'start' => '14:00',
                    'end' => '19:00',
                ],
            ],
            'workShortDaySchedule' => [
                [
                    'start' => '10:00',
                    'end' => '13:00',
                ],
                [
                    'start' => '14:00',
                    'end' => '18:00',
                ],
            ],
            'workDayNonWorkSchedule' => [
                [
                    'start' => '00:00',
                    'end' => '10:00',
                ],
                [
                    'start' => '13:00',
                    'end' => '14:00',
                ],
                [
                    'start' => '19:00',
                    'end' => '24:00',
                ],
            ],
            'workShortDayNonWorkSchedule' => [
                [
                    'start' => '00:00',
                    'end' => '10:00',
                ],
                [
                    'start' => '13:00',
                    'end' => '14:00',
                ],
                [
                    'start' => '18:00',
                    'end' => '24:00',
                ],
            ],
            'offDayNonWorkSchedule' => [
                [
                    'start' => '00:00',
                    'end' => '24:00',
                ],
            ],
        ];

        yield 'Employee who works from the early morning' => [
            'id' => '2',
            'workDaySchedule' => [
                [
                    'start' => '09:00',
                    'end' => '12:00',
                ],
                [
                    'start' => '13:00',
                    'end' => '18:00',
                ],
            ],
            'workShortDaySchedule' => [
                [
                    'start' => '09:00',
                    'end' => '12:00',
                ],
                [
                    'start' => '13:00',
                    'end' => '17:00',
                ],
            ],
            'workDayNonWorkSchedule' => [
                [
                    'start' => '00:00',
                    'end' => '09:00',
                ],
                [
                    'start' => '12:00',
                    'end' => '13:00',
                ],
                [
                    'start' => '18:00',
                    'end' => '24:00',
                ],
            ],
            'workShortDayNonWorkSchedule' => [
                [
                    'start' => '00:00',
                    'end' => '09:00',
                ],
                [
                    'start' => '12:00',
                    'end' => '13:00',
                ],
                [
                    'start' => '17:00',
                    'end' => '24:00',
                ],
            ],
            'offDayNonWorkSchedule' => [
                [
                    'start' => '00:00',
                    'end' => '24:00',
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    protected function getJsonResponseDataOrNull(JsonResponse $response): ?array
    {
        $responseContent = $response->getContent();

        return $responseContent ? json_decode($responseContent, true) : null;
    }

    /**
     * @param mixed[] $schedule
     *
     * @return string[][]|null
     */
    protected function findDaySchedule(string $day, array $schedule): ?array
    {
        foreach ($schedule as $daySchedule) {
            if ($daySchedule['day'] === $day) {
                return $daySchedule;
            }
        }

        return null;
    }

    protected function assertResponseContainsSchedule(JsonResponse $response): void
    {
        $responseData = $this->getJsonResponseDataOrNull($response);

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('schedule', $responseData);

        $schedule = $responseData['schedule'];

        $this->assertIsArray($schedule);
        $this->assertNotCount(0, $schedule);

        foreach ($schedule as $daySchedule) {
            $this->assertDayScheduleIsValid($daySchedule);
        }
    }

    /**
     * @param mixed[] $daySchedule
     */
    protected function assertDayScheduleIsValid(array $daySchedule): void
    {
        $this->assertArrayHasKey('day', $daySchedule);
        $this->assertArrayHasKey('timeRanges', $daySchedule);

        $day = $daySchedule['day'];
        $timeRanges = $daySchedule['timeRanges'];

        $this->assertIsString($day);
        $this->assertIsArray($timeRanges);
        $this->assertNotCount(0, $timeRanges);

        foreach ($timeRanges as $timeRange) {
            $this->assertTimeRangeIsValid($timeRange);
        }
    }

    /**
     * @param mixed[] $timeRange
     */
    protected function assertTimeRangeIsValid(array $timeRange): void
    {
        $this->assertArrayHasKey('start', $timeRange);
        $this->assertArrayHasKey('end', $timeRange);

        $startTime = $timeRange['start'];
        $endTime = $timeRange['end'];

        $this->assertIsString($startTime);
        $this->assertIsString($endTime);
    }

    protected function assertResponseContainsErrors(JsonResponse $response): void
    {
        $responseData = $this->getJsonResponseDataOrNull($response);

        $this->assertArrayHasKey('errors', $responseData);

        $errors = $responseData['errors'];

        $this->assertIsArray($errors);
        $this->assertNotCount(0, $errors);
    }
}

