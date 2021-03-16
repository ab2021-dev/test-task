<?php

namespace App\Tests\Controller;

use App\Exception\CalendarApiException;
use App\Service\ExternalApi\CalendarInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeScheduleControllerTest extends BaseEmployeeScheduleControllerTest
{
    public const GET_EMPLOYEE_SCHEDULE_ENDPOINT_URL = '/employee-schedule';

    /** @dataProvider getEmployeesData */
    public function testScheduleMustBeInValidFormat(string $employeeId): void
    {
        $response = $this->requestSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertResponseContainsSchedule($response);
    }

    private function requestSchedule(?string $employeeId = null, ?string $startDate = null, ?string $endDate = null): Response
    {
        $requestParams = [];

        if (!is_null($employeeId)) {
            $requestParams['employeeId'] = $employeeId;
        }

        if (!is_null($startDate)) {
            $requestParams['startDate'] = $startDate;
        }

        if (!is_null($endDate)) {
            $requestParams['endDate'] = $endDate;
        }

        $this->client->request(Request::METHOD_GET, self::GET_EMPLOYEE_SCHEDULE_ENDPOINT_URL, $requestParams);
        return $this->client->getResponse();
    }

    /** @dataProvider getEmployeesData */
    public function testWorkDayScheduleMustContainsEmployeeSchedule(string $employeeId, array $employeeWorkDaySchedule): void
    {
        $response = $this->requestSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? [];

        $workDaySchedule = $this->findDaySchedule(self::WORK_DAY_DATE, $schedule);
        $this->assertNotEmpty($workDaySchedule);
        $this->assertEquals($employeeWorkDaySchedule, $workDaySchedule['timeRanges']);
    }

    /** @dataProvider getEmployeesData */
    public function testWorkShortDayScheduleMustContainsEmployeeSchedule(string $employeeId, array $_, array $employeeWorkShortDaySchedule): void
    {
        $response = $this->requestSchedule($employeeId, self::SHORT_WORK_DAY_DATE, self::SHORT_WORK_DAY_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? [];

        $workShortDaySchedule = $this->findDaySchedule(self::SHORT_WORK_DAY_DATE, $schedule);
        $this->assertNotEmpty($workShortDaySchedule);
        $this->assertEquals($employeeWorkShortDaySchedule, $workShortDaySchedule['timeRanges']);
    }

    /** @dataProvider getEmployeesData */
    public function testWeekendDayScheduleMustBeExcludedFromWorkSchedule(string $employeeId): void
    {
        $response = $this->requestSchedule($employeeId, self::WEEKEND_DATE, self::WEEKEND_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? null;

        $this->assertIsArray($schedule);
        $this->assertNull($this->findDaySchedule(self::WEEKEND_DATE, $schedule));
    }

    /** @dataProvider getEmployeesData */
    public function testHolidayDayScheduleMustBeExcludedFromWorkSchedule(string $employeeId): void
    {
        $response = $this->requestSchedule($employeeId, self::HOLIDAY_DATE, self::WORK_DAY_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? null;

        $this->assertIsArray($schedule);
        $this->assertNull($this->findDaySchedule(self::HOLIDAY_DATE, $schedule));
        $this->assertNotNull($this->findDaySchedule(self::WORK_DAY_DATE, $schedule));
    }

    /** @dataProvider getEmployeesData */
    public function testWeekendMustBeExcludedFromWorkSchedule(string $employeeId): void
    {
        $response = $this->requestSchedule($employeeId, self::WEEKEND_START_DATE, self::WEEKEND_END_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? null;

        $this->assertIsArray($schedule);
        $this->assertNull($this->findDaySchedule(self::WEEKEND_START_DATE, $schedule));
        $this->assertNull($this->findDaySchedule(self::WEEKEND_END_DATE, $schedule));
    }

    /** @dataProvider getEmployeesData */
    public function testHolidaysMustBeExcludedFromSchedule(string $employeeId): void
    {
        $response = $this->requestSchedule($employeeId, self::HOLIDAY_DATE, self::HOLIDAY_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? null;

        $this->assertIsArray($schedule);
        $this->assertNull($this->findDaySchedule(self::HOLIDAY_DATE, $schedule));
    }

    /** @dataProvider getEmployeesData */
    public function testInvalidRequestMustReturnErrors(string $employeeId): void
    {
        $invalidDate = 'invalid date';
        $response = $this->requestSchedule($employeeId, $invalidDate, $invalidDate);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    public function testEmptyRequestParamsMustReturnErrors(): void
    {
        $response = $this->requestSchedule();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testWrongStartEndDateIntervalMustReturnErrors(string $employeeId): void
    {
        $response = $this->requestSchedule($employeeId, self::NEXT_WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    public function testWrongEmployeeIdMustReturnErrors(): void
    {
        $response = $this->requestSchedule(self::WRONG_EMPLOYEE_ID, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testWorkScheduleRequestWithUnavailableCalendarServiceMustReturnErrors(string $employeeId): void
    {
        $calendarServiceStub = $this->createMock(CalendarInterface::class);
        $calendarServiceStub->method('isDayOff')->willThrowException(
            new CalendarApiException(
                JsonResponse::HTTP_SERVICE_UNAVAILABLE,
                self::CALENDAR_SERVICE_IS_NOT_AVAILABLE_MESSAGE
            )
        );
        $this->client->getContainer()->set(self::CALENDAR_SERVICE_ID, $calendarServiceStub);

        $response = $this->requestSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testScheduleRequestWithNotExistEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $this->renamedEmployeesFilePath = $this->originalEmployeesFilePath . '_copy';
        rename($this->originalEmployeesFilePath, $this->renamedEmployeesFilePath);

        $response = $this->requestSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testScheduleRequestWithUnreadableEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $path = $this->client->getContainer()->getParameter('employees_data_file_path');
        chmod($path, self::EMPTY_EMPLOYEES_FILE_PERMISSIONS);
        $this->isChangedEmployeesFilePermissions = true;

        $response = $this->requestSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testScheduleRequestWithEmptyEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $path = $this->client->getContainer()->getParameter('employees_data_file_path');
        file_put_contents($path, '');
        $this->isChangedEmployeesFileContent = true;

        $response = $this->requestSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testScheduleRequestWithIncorrectEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $path = $this->client->getContainer()->getParameter('employees_data_file_path');
        file_put_contents($path, '{}');
        $this->isChangedEmployeesFileContent = true;

        $response = $this->requestSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testScheduleRequestWithIncompleteEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $path = $this->client->getContainer()->getParameter('employees_data_file_path');
        file_put_contents($path, '[{"id": "1"}, {"id": "2"}]');
        $this->isChangedEmployeesFileContent = true;

        $response = $this->requestSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }
}

