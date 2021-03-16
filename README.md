# Installation

1. Clone repository `git clone https://github.com/ab12320/symfony-test-task-employee-schedule.git`.
2. Install [docker](https://docs.docker.com/engine/install/ubuntu/) and [docker-compose](https://docs.docker.com/compose/install/).
3. In the folder `symfony-test-task-employee-schedule` run command `docker-compose up --build -d`.
4. Run command `docker-compose exec workspace sh`.
5. Install dependencies by command `composer install`.

Service will be available by http://localhost/employee-schedule.

Console will be available in the container `workspace`: `docker-compose exec workspace sh`, `php bin/console`.

Tests: `docker-compose exec workspace sh`, `./bin/phpunit`.

# Task
You need to develop a small web service that will be responsible for calculating the working schedule of employees.

Company employee schedule.
The service receives the date period and the employee id as get parameters: EmployeeID, StartDate, EndDate.

In response, the service must send the employee's work schedule in JSON format. The schedule should not be included:
public holidays and weekends
lunch break
time before the start of the working day and after the working day.

Response example:

GET http://localhost/employee-schedule?startDate=2021-01-01&endDate=2021-01-12&employeeId=1
`{
"schedule": [
{
"day":"2021-01-11",
"timeRanges": [
{
"start":"10:00",
"end":"13:00"
},
{
"start":"14:00",
"end":"19:00"
}
]
},
{
"day":"2021-01-12",
"timeRanges": [
{
"start":"10:00",
"end":"13:00"
},
{
"start":"14:00",
"end":"19:00"
}
]
}
]
}`
To exclude public holidays from the work schedule, use any open API source (for example, the Google calendar API, xmlcalendar.ru, isdayoff.ru...).

Working with the database is not necessary, however, think about how you will store information about the employee's work schedule.

For individual employee data, use the following parameters (or any similar parameters):):

Employee №1

Schedule (10:00 - 13:00 [lunch] 14:00 - 19:00).

Working days from Monday to Friday.

Employee №2

Schedule (09:00 - 12:00 [lunch] 13:00 - 18:00).

Working days from Monday to Friday.

# Requirements and explanations for the task

The code should be written in the OO style, be clean and easy to read.
When performing a task, you can use any open source software (frameworks, components).
Add an endpoint that returns the employee's non-working schedule using the same input parameters (in the same format as the working schedule).
