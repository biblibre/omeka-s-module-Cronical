<?php

namespace Cronical\Test\Stdlib;

use Cronical\Test\TestCase;
use Cronical\Stdlib\CronExpression;
use DateTime;
use DateTimeZone;
use Exception;

class CronExpressionTest extends TestCase
{
    public function testCronExpression()
    {
        $this->assertCronExpressionValid('* * * * *');
        $this->assertCronExpressionValid('0 * * * *');
        $this->assertCronExpressionValid('30 * * * *');
        $this->assertCronExpressionValid('0-45 * * * *');
        $this->assertCronExpressionValid('30-45 * * * *');
        $this->assertCronExpressionValid('30-45 * * * *');
        $this->assertCronExpressionValid('0-45/5 * * * *');
        $this->assertCronExpressionValid('0-45/15 * * * *');
        $this->assertCronExpressionValid('0-45/15,57,58 * * * *');
        $this->assertCronExpressionValid('*/15,57,58 * * * *');
        $this->assertCronExpressionValid('* 0 * * *');
        $this->assertCronExpressionValid('* 10 * * *');
        $this->assertCronExpressionValid('* 08-17 * * *');
        $this->assertCronExpressionValid('* 08-17/2 * * *');
        $this->assertCronExpressionValid('* 08-17/2,*/8 * * *');
        $this->assertCronExpressionValid('* * 1 * *');
        $this->assertCronExpressionValid('* * 1-31 * *');
        $this->assertCronExpressionValid('* * 1-31/5 * *');
        $this->assertCronExpressionValid('* * 1-31/5,28 * *');
        $this->assertCronExpressionValid('* * */5,28 * *');
        $this->assertCronExpressionValid('* * * 1 *');
        $this->assertCronExpressionValid('* * * 1-12 *');
        $this->assertCronExpressionValid('* * * 1-12/3 *');
        $this->assertCronExpressionValid('* * * 1-12/3,12 *');
        $this->assertCronExpressionValid('* * * */3 *');
        $this->assertCronExpressionValid('* * * FEB *');
        $this->assertCronExpressionValid('* * * feb *');
        $this->assertCronExpressionValid('* * * feb-jun/2 *');
        $this->assertCronExpressionValid('* * * 2-jun/2 *');
        $this->assertCronExpressionValid('* * * * 0');
        $this->assertCronExpressionValid('* * * * 6');
        $this->assertCronExpressionValid('* * * * 2-4');
        $this->assertCronExpressionValid('* * * * 1-5/2');
        $this->assertCronExpressionValid('* * * * */2');
        $this->assertCronExpressionValid('* * * * 1-5/2,0');
        $this->assertCronExpressionValid('* * * * sun');
        $this->assertCronExpressionValid('* * * * sun-fri');
        $this->assertCronExpressionValid('* * * * mon,tue,wed');
    }

    public function testInvalidCronExpression()
    {
        $this->assertCronExpressionNotValid('* * * *');
        $this->assertCronExpressionNotValid('* * * * * *');
        $this->assertCronExpressionNotValid('60 * * * *');
        $this->assertCronExpressionNotValid('30-15 * * * *');
        $this->assertCronExpressionNotValid('* 24 * * *');
        $this->assertCronExpressionNotValid('* 17-08 * * *');
        $this->assertCronExpressionNotValid('* * 0 * *');
        $this->assertCronExpressionNotValid('* * 32 * *');
        $this->assertCronExpressionNotValid('* * 15-10 * *');
        $this->assertCronExpressionNotValid('* * * 0 *');
        $this->assertCronExpressionNotValid('* * * 13 *');
        $this->assertCronExpressionNotValid('* * * 6-2 *');
        $this->assertCronExpressionNotValid('* * * jun-feb *');
        $this->assertCronExpressionNotValid('* * * * 7');
        $this->assertCronExpressionNotValid('* * * * 5-1');
        $this->assertCronExpressionNotValid('* * * * fri-sun');
        $this->assertCronExpressionNotValid('* * * * fri-mon');
    }

    public function testGetNextDate()
    {
        $this->assertEquals(
            '2024-01-01 12:35',
            $this->getNextDate('* * * * *', '2024-01-01 12:34'),
            'every minute',
        );

        $this->assertEquals(
            '2024-01-02 00:00',
            $this->getNextDate('* * * * *', '2024-01-01 23:59'),
            'every minute, but the next minute is the next day',
        );

        $this->assertEquals(
            '2024-01-02 00:00',
            $this->getNextDate('0 * * * *', '2024-01-01 23:32'),
            'at minute 0',
        );

        $this->assertEquals(
            '2024-01-02 04:00',
            $this->getNextDate('0 4-6 * * *', '2024-01-01 23:32'),
            'at minute 0, past every hour from 4 through 6',
        );

        $this->assertEquals(
            '2024-01-01 13:45',
            $this->getNextDate('45 10-14/3 * * *', '2024-01-01 12:01'),
            'At minute 45 past every 3rd hour from 10 through 14',
        );

        $this->assertEquals(
            '2052-02-29 00:00',
            $this->getNextDate('0 0 29 2 4', '2024-03-01 00:00'),
            'The next February 29th that is also a Thursday',
        );

        $this->assertEquals(
            '2025-03-01 00:00',
            $this->getNextDate('0 0 1-28,31 * *', '2025-02-28 00:00'),
            'Edge case: make sure we check dates in order',
        );

        $this->assertEquals(
            '2026-01-01 00:00',
            $this->getNextDate('0 0 1 1 *', '2025-01-01 00:00'),
        );

        $this->assertEquals(
            '2025-01-01 00:01',
            $this->getNextDate('1 0 1 1 *', '2025-01-01 00:00'),
        );

        $this->assertEquals(
            '2025-03-05 00:00',
            $this->getNextDate('0 0 * mar wed', '2025-01-01 00:00'),
        );

        $this->assertEquals(
            '2025-01-01 22:00',
            $this->getNextDate('0 0 * * *', '2025-01-01 21:00', '+0200'),
            'Timezone should be taken care of',
        );

        $this->assertEquals(
            '2025-01-02 22:00',
            $this->getNextDate('0 0 * * *', '2025-01-01 23:00', '+0200'),
            'Timezone should be taken care of',
        );
    }

    protected function getNextDate(string $expression, string $now, string $timezone = 'UTC'): string
    {
        $timezone = new DateTimeZone($timezone);
        $cronExpr = new CronExpression($expression, $timezone);

        return $cronExpr->getNextDate(new DateTime($now))->format('Y-m-d H:i');
    }

    protected function assertCronExpressionValid(string $expression)
    {
        $timezone = new DateTimeZone('UTC');
        $exception = null;
        try {
            new CronExpression($expression, $timezone);
        } catch (Exception $exception) {
        }
        $this->assertNull($exception);
    }

    protected function assertCronExpressionNotValid(string $expression)
    {
        $timezone = new DateTimeZone('UTC');
        $exception = null;
        try {
            new CronExpression($expression, $timezone);
        } catch (Exception $exception) {
        }
        $this->assertNotNull($exception);
    }
}
