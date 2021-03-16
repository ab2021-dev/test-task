<?php

namespace App\Tests\Controller;

use App\Exception\CalendarApiException;
use App\Service\ExternalApi\CalendarInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeNonWorkScheduleControllerTest extends BaseEmployeeScheduleControllerTest
{
    public const GET_EMPLOYEE_NON_WORK_SCHEDULE_ENDPOINT_URL = '/employee-schedule/non-work';

    /** @dataProvider getEmployeesData */
    public function testNonWorkScheduleMustBeInValidFormat(string $employeeId): void
    {
        $response = $this->requestNonWorkSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertResponseContainsSchedule($response);
    }

    private function requestNonWorkSchedule(?string $employeeId = null, ?string $startDate = null, ?string $endDate = null): Response
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

        $this->client->request(Request::METHOD_GET, self::GET_EMPLOYEE_NON_WORK_SCHEDULE_ENDPOINT_URL, $requestParams);
        return $this->client->getResponse();
    }

    /** @dataProvider getEmployeesData */
    public function testWorkDayScheduleMustContainsEmployeeNonWorkSchedule(string $employeeId, array $_, array $__, array $employeeWorkDayNonWorkSchedule): void
    {
        $response = $this->requestNonWorkSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? [];

        $workDayNonWorkSchedule = $this->findDaySchedule(self::WORK_DAY_DATE, $schedule);
        $this->assertNotEmpty($workDayNonWorkSchedule);
        $this->assertEquals($employeeWorkDayNonWorkSchedule, $workDayNonWorkSchedule['timeRanges']);
    }

    /** @dataProvider getEmployeesData */
    public function testWorkShortDayScheduleMustContainsEmployeeNonWorkSchedule(string $employeeId, array $_, array $__, array $___, array $employeeWorkShortDayNonWorkSchedule): void
    {
        $response = $this->requestNonWorkSchedule($employeeId, self::SHORT_WORK_DAY_DATE, self::SHORT_WORK_DAY_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? [];

        $workShortDayNonWorkSchedule = $this->findDaySchedule(self::SHORT_WORK_DAY_DATE, $schedule);
        $this->assertNotEmpty($workShortDayNonWorkSchedule);
        $this->assertEquals($employeeWorkShortDayNonWorkSchedule, $workShortDayNonWorkSchedule['timeRanges']);
    }

    /** @dataProvider getEmployeesData */
    public function testWeekendDayScheduleMustContainsEmployeeNonWorkSchedule(string $employeeId, array $_, array $__, array $___, array $____, array $employeeOffDayNonWorkSchedule): void
    {
        $response = $this->requestNonWorkSchedule($employeeId, self::WEEKEND_DATE, self::WEEKEND_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? [];

        $weekendDaySchedule = $this->findDaySchedule(self::WEEKEND_DATE, $schedule);
        $this->assertNotEmpty($weekendDaySchedule);
        $this->assertEquals($employeeOffDayNonWorkSchedule, $weekendDaySchedule['timeRanges']);
    }

    /** @dataProvider getEmployeesData */
    public function testHolidayDayScheduleMustContainsEmployeeNonWorkSchedule(string $employeeId, array $_, array $__, array $___, array $____, array $employeeOffDayNonWorkSchedule): void
    {
        $response = $this->requestNonWorkSchedule($employeeId, self::HOLIDAY_DATE, self::HOLIDAY_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? [];

        $holidayDaySchedule = $this->findDaySchedule(self::HOLIDAY_DATE, $schedule);
        $this->assertNotEmpty($holidayDaySchedule);
        $this->assertEquals($employeeOffDayNonWorkSchedule, $holidayDaySchedule['timeRanges']);
    }

    /** @dataProvider getEmployeesData */
    public function testWeekendMustBeIncludedToNonWorkSchedule(string $employeeId): void
    {
        $response = $this->requestNonWorkSchedule($employeeId, self::WEEKEND_START_DATE, self::WEEKEND_END_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? null;

        $this->assertIsArray($schedule);
        $this->assertNotNull($this->findDaySchedule(self::WEEKEND_START_DATE, $schedule));
        $this->assertNotNull($this->findDaySchedule(self::WEEKEND_END_DATE, $schedule));
    }

    /** @dataProvider getEmployeesData */
    public function testHolidaysMustBeIncludedToNonWorkSchedule(string $employeeId): void
    {
        $response = $this->requestNonWorkSchedule($employeeId, self::HOLIDAY_DATE, self::WORK_DAY_DATE);
        $responseData = $this->getJsonResponseDataOrNull($response);
        $schedule = $responseData['schedule'] ?? null;

        $this->assertIsArray($schedule);
        $this->assertNotNull($this->findDaySchedule(self::HOLIDAY_DATE, $schedule));
        $this->assertNotNull($this->findDaySchedule(self::WORK_DAY_DATE, $schedule));
    }

    /** @dataProvider getEmployeesData */
    public function testInvalidRequestToNonWorkScheduleMustReturnErrors(string $employeeId): void
    {
        $invalidDate = 'invalid date';

        $response = $this->requestNonWorkSchedule($employeeId, $invalidDate, $invalidDate);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    public function testEmptyRequestParamsMustReturnErrors(): void
    {
        $response = $this->requestNonWorkSchedule();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testWrongStartEndDateIntervalMustReturnErrors(string $employeeId): void
    {
        $response = $this->requestNonWorkSchedule($employeeId, self::NEXT_WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    public function testWrongEmployeeIdMustReturnErrors(): void
    {
        $response = $this->requestNonWorkSchedule(self::WRONG_EMPLOYEE_ID, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testNonWorkScheduleRequestWithUnavailableCalendarInterfaceMustReturnErrors(string $employeeId): void
    {
//        $this->expectException(CalendarApiException::class);
        $calendarServiceStub = $this->createMock(CalendarInterface::class);
        $calendarServiceStub->method('isDayWorkingButShort')->willThrowException(
            new CalendarApiException(
                JsonResponse::HTTP_SERVICE_UNAVAILABLE,
                self::CALENDAR_SERVICE_IS_NOT_AVAILABLE_MESSAGE
            )
        );
        $this->client->getContainer()->set(self::CALENDAR_SERVICE_ID, $calendarServiceStub);

        $response = $this->requestNonWorkSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testNonWorkScheduleRequestWithNotExistEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $this->renamedEmployeesFilePath = $this->originalEmployeesFilePath . '_copy';
        rename($this->originalEmployeesFilePath, $this->renamedEmployeesFilePath);

        $response = $this->requestNonWorkSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testNonWorkScheduleRequestWithUnreadableEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $path = $this->client->getContainer()->getParameter('employees_data_file_path');
        chmod($path, self::EMPTY_EMPLOYEES_FILE_PERMISSIONS);
        $this->isChangedEmployeesFilePermissions = true;

        $response = $this->requestNonWorkSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testNonWorkScheduleRequestWithEmptyEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $path = $this->client->getContainer()->getParameter('employees_data_file_path');
        file_put_contents($path, '');
        $this->isChangedEmployeesFileContent = true;

        $response = $this->requestNonWorkSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testNonWorkScheduleRequestWithIncorrectEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $path = $this->client->getContainer()->getParameter('employees_data_file_path');
        file_put_contents($path, '{}');
        $this->isChangedEmployeesFileContent = true;

        $response = $this->requestNonWorkSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }

    /** @dataProvider getEmployeesData */
    public function testNonWorkScheduleRequestWithIncompleteEmployeesFileMustReturnErrors(string $employeeId): void
    {
        $path = $this->client->getContainer()->getParameter('employees_data_file_path');
        file_put_contents($path, '[{"id": "1"}, {"id": "2"}]');
        $this->isChangedEmployeesFileContent = true;

        $response = $this->requestNonWorkSchedule($employeeId, self::WORK_DAY_DATE, self::WORK_DAY_DATE);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertResponseContainsErrors($response);
    }
}

