<?php

namespace Cronical\Stdlib;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

class CronExpression
{
    public readonly string $minute;
    public readonly string $hour;
    public readonly string $dayOfMonth;
    public readonly string $month;
    public readonly string $dayOfWeek;

    protected readonly array $allowedMinutes;
    protected readonly array $allowedHours;
    protected readonly array $allowedDayOfMonths;
    protected readonly array $allowedMonths;
    protected readonly array $allowedDayOfWeeks;

    protected readonly DateTimeZone $timezone;

    public const MONTH_ALIASES = [
        'JAN' => 1,
        'FEB' => 2,
        'MAR' => 3,
        'APR' => 4,
        'MAY' => 5,
        'JUN' => 6,
        'JUL' => 7,
        'AUG' => 8,
        'SEP' => 9,
        'OCT' => 10,
        'NOV' => 11,
        'DEC' => 12,
    ];

    public const DAY_OF_WEEK_ALIASES = [
        'SUN' => 0,
        'MON' => 1,
        'TUE' => 2,
        'WED' => 3,
        'THU' => 4,
        'FRI' => 5,
        'SAT' => 6,
    ];

    public function __construct(string $expression, DateTimeZone $timezone)
    {
        $parts = explode(' ', $expression);
        if (count($parts) !== 5) {
            throw new Exception(sprintf('Invalid cron expression: %s', $expression));
        }

        [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = $parts;

        self::validateMinuteExpression($minute);
        self::validateHourExpression($hour);
        self::validateDayOfMonthExpression($dayOfMonth);
        self::validateMonthExpression($month);
        self::validateDayOfWeekExpression($dayOfWeek);

        $this->minute = $minute;
        $this->hour = $hour;
        $this->dayOfMonth = $dayOfMonth;
        $this->month = $month;
        $this->dayOfWeek = $dayOfWeek;

        $this->allowedMinutes = $this->getAllowedValues($this->minute, 0, 59);
        $this->allowedHours = $this->getAllowedValues($this->hour, 0, 23);
        $this->allowedDayOfMonths = $this->getAllowedValues($this->dayOfMonth, 1, 31);
        $this->allowedMonths = $this->getAllowedValues($this->month, 1, 12, fn ($month) => self::resolveMonthAlias($month));
        $this->allowedDayOfWeeks = $this->getAllowedValues($this->dayOfWeek, 0, 6, fn ($month) => self::resolveDayOfWeekAlias($month));

        $this->timezone = $timezone;
    }

    public static function validateMinuteExpression(string $expression): void
    {
        if (!preg_match('/^(?<value>(\*|(?<number>0?[0-9]|[1-5][0-9])(-(?&number))?)(\/\d+)?)(,(?&value))*$/', $expression)) {
            throw new Exception(sprintf('Invalid minute expression: %s', $expression));
        }

        $values = explode(',', $expression);
        foreach ($values as $value) {
            if (preg_match('/(\d+)-(\d+)/', $value, $matches)) {
                if ($matches[1] > $matches[2]) {
                    throw new Exception(sprintf('Invalid range: %s', $matches[0]));
                }
            }
        }
    }

    public static function validateHourExpression(string $expression): void
    {
        if (!preg_match('/^(?<value>(\*|(?<number>0?[0-9]|1[0-9]|2[0-3])(-(?&number))?)(\/\d+)?)(,(?&value))*$/', $expression)) {
            throw new Exception(sprintf('Invalid hour expression: %s', $expression));
        }

        $values = explode(',', $expression);
        foreach ($values as $value) {
            if (preg_match('/(\d+)-(\d+)/', $value, $matches)) {
                if ($matches[1] > $matches[2]) {
                    throw new Exception(sprintf('Invalid range: %s', $matches[0]));
                }
            }
        }
    }

    public static function validateDayOfMonthExpression(string $expression): void
    {
        if (!preg_match('/^(?<value>(\*|(?<number>0?[1-9]|[1-2][0-9]|3[0-1])(-(?&number))?)(\/\d+)?)(,(?&value))*$/', $expression)) {
            throw new Exception(sprintf('Invalid day of month expression: %s', $expression));
        }

        $values = explode(',', $expression);
        foreach ($values as $value) {
            if (preg_match('/(\d+)-(\d+)/', $value, $matches)) {
                if ($matches[1] > $matches[2]) {
                    throw new Exception(sprintf('Invalid range: %s', $matches[0]));
                }
            }
        }
    }

    public static function validateMonthExpression(string $expression): void
    {
        $monthAliasRegex = implode('|', array_keys(self::MONTH_ALIASES));
        if (!preg_match("/^(?<value>(\*|(?<month>0?[1-9]|1[0-2]|$monthAliasRegex)(-(?&month))?)(\/\d+)?)(,(?&value))*$/i", $expression)) {
            throw new Exception(sprintf('Invalid month expression: %s', $expression));
        }

        $values = explode(',', $expression);
        foreach ($values as $value) {
            if (preg_match("/(?<month>0?[1-9]|1[0-2]|$monthAliasRegex)-((?&month))/i", $value, $matches)) {
                [$range, $start, $end] = $matches;
                $start = self::resolveMonthAlias($start);
                $end = self::resolveMonthAlias($end);
                if ($start > $end) {
                    throw new Exception(sprintf('Invalid range: %s', $range));
                }
            }
        }
    }

    public static function validateDayOfWeekExpression(string $expression): void
    {
        $dayOfWeekAliasRegex = implode('|', array_keys(self::DAY_OF_WEEK_ALIASES));
        if (!preg_match("/^(?<value>(\*|(?<dayOfWeek>0?[0-6]|$dayOfWeekAliasRegex)(-(?&dayOfWeek))?)(\/\d+)?)(,(?&value))*$/i", $expression)) {
            throw new Exception(sprintf('Invalid day of week expression: %s', $expression));
        }

        $values = explode(',', $expression);
        foreach ($values as $value) {
            if (preg_match("/(?<dayOfWeek>0?[0-6]|$dayOfWeekAliasRegex)-((?&dayOfWeek))/i", $value, $matches)) {
                [$range, $start, $end] = $matches;
                $start = self::resolveDayOfWeekAlias($start);
                $end = self::resolveDayOfWeekAlias($end);
                if ($start > $end) {
                    throw new Exception(sprintf('Invalid range: %s', $range));
                }
            }
        }
    }

    public static function resolveMonthAlias(string $month): int
    {
        if (is_numeric($month)) {
            return intval($month);
        }

        $month = strtoupper($month);
        if (array_key_exists($month, self::MONTH_ALIASES)) {
            return self::MONTH_ALIASES[$month];
        }

        throw new Exception(sprintf('Invalid month: %s', $month));
    }

    public static function resolveDayOfWeekAlias(string $dayOfWeek): int
    {
        if (is_numeric($dayOfWeek)) {
            return intval($dayOfWeek);
        }

        $dayOfWeek = strtoupper($dayOfWeek);
        if (array_key_exists($dayOfWeek, self::DAY_OF_WEEK_ALIASES)) {
            return self::DAY_OF_WEEK_ALIASES[$dayOfWeek];
        }

        throw new Exception(sprintf('Invalid day of week: %s', $dayOfWeek));
    }

    public function getNextDate(?DateTimeInterface $now = null): DateTime
    {
        $next = $now ? DateTime::createFromInterface($now) : new DateTime();
        $originalTimezone = $next->getTimezone();
        $next->setTimezone($this->timezone);

        $next->modify('0 second 0 usec');

        $minute = intval($next->format('i'));
        $hour = intval($next->format('G'));
        $dayOfMonth = intval($next->format('j'));
        $month = intval($next->format('n'));
        $dayOfWeek = intval($next->format('w'));

        $allowedMinutes = $this->getAllowedMinutes();
        $allowedHours = $this->getAllowedHours();
        $allowedDayOfMonths = $this->getAllowedDayOfMonths();
        $allowedMonths = $this->getAllowedMonths();
        $allowedDayOfWeeks = $this->getAllowedDayOfWeeks();

        if (in_array($dayOfMonth, $allowedDayOfMonths) && in_array($month, $allowedMonths) && in_array($dayOfWeek, $allowedDayOfWeeks)) {
            // Try to find a time on the same day
            if (in_array($hour, $allowedHours)) {
                $possibleMinutes = array_filter($allowedMinutes, fn ($allowedMinute) => $allowedMinute > $minute);
                if ($possibleMinutes) {
                    $next->setTime($hour, reset($possibleMinutes));
                    $next->setTimezone($originalTimezone);

                    return $next;
                }
            }

            $possibleHours = array_filter($allowedHours, fn ($allowedHour) => $allowedHour > $hour);
            if ($possibleHours) {
                $next->setTime(reset($possibleHours), reset($allowedMinutes));
                $next->setTimezone($originalTimezone);

                return $next;
            }
        }

        // We couldn't find a time on the same day
        // Reset the time and search a new date

        $next->setTime(reset($allowedHours), reset($allowedMinutes));

        $year = intval($next->format('Y'));

        $allowedMonthIndex = 0;
        $allowedDayOfMonthIndex = 0;

        // Find the index of the current or the next month in the list of allowed months
        while (isset($allowedMonths[$allowedMonthIndex]) && $allowedMonths[$allowedMonthIndex] < $month) {
            $allowedMonthIndex++;
        }

        // Find the index of the next day of the month in the list of allowed days of the month
        while (isset($allowedDayOfMonths[$allowedDayOfMonthIndex]) && $allowedDayOfMonths[$allowedDayOfMonthIndex] <= $dayOfMonth) {
            $allowedDayOfMonthIndex++;
        }

        if ($allowedDayOfMonthIndex >= count($allowedDayOfMonths)) {
            $allowedDayOfMonthIndex = 0;
            $allowedMonthIndex++;
        }
        if ($allowedMonthIndex >= count($allowedMonths)) {
            $allowedDayOfMonthIndex = 0;
            $allowedMonthIndex = 0;
            $year++;
        }

        while (true) {
            $month = $allowedMonths[$allowedMonthIndex];
            $dayOfMonth = $allowedDayOfMonths[$allowedDayOfMonthIndex];

            $next->setDate($year, $month, $dayOfMonth);

            $allowedDayOfMonthIndex++;
            if ($allowedDayOfMonthIndex >= count($allowedDayOfMonths)) {
                $allowedDayOfMonthIndex = 0;
                $allowedMonthIndex++;
                if ($allowedMonthIndex >= count($allowedMonths)) {
                    $allowedMonthIndex = 0;
                    $year++;
                }
            }

            // Result of setDate($year, $month, $day) can be different from
            // $year-$month-$day (for instance setDate(2025, 2, 31))
            // We don't want to test a date different from what we set so we just skip it
            if (intval($next->format('j')) !== $dayOfMonth || intval($next->format('n')) !== $month) {
                continue;
            }

            if ($this->isDateAllowed($next)) {
                break;
            }
        }

        $next->setTimezone($originalTimezone);

        return $next;
    }

    /**
     * Check if the date part of $datetime is allowed by the cron expression
     */
    public function isDateAllowed(DateTimeInterface $datetime): bool
    {
        $datetime = DateTime::createFromInterface($datetime);
        $originalTimezone = $datetime->getTimezone();
        $datetime->setTimezone($this->timezone);

        $dayOfMonth = intval($datetime->format('j'));
        $month = intval($datetime->format('n'));
        $dayOfWeek = intval($datetime->format('w'));

        $dayOfMonthIsAllowed = in_array($dayOfMonth, $this->allowedDayOfMonths, true);
        $monthIsAllowed = in_array($month, $this->allowedMonths, true);
        $dayOfWeekIsAllowed = in_array($dayOfWeek, $this->allowedDayOfWeeks, true);

        return $dayOfMonthIsAllowed && $monthIsAllowed && $dayOfWeekIsAllowed;
    }

    public function getAllowedMinutes(): array
    {
        return $this->allowedMinutes;
    }

    public function getAllowedHours(): array
    {
        return $this->allowedHours;
    }

    public function getAllowedDayOfMonths(): array
    {
        return $this->allowedDayOfMonths;
    }

    public function getAllowedMonths(): array
    {
        return $this->allowedMonths;
    }

    public function getAllowedDayOfWeeks(): array
    {
        return $this->allowedDayOfWeeks;
    }

    protected function getAllowedValues(string $expression, int $lowerBound, int $upperBound, ?callable $resolveAlias = null): array
    {
        $allowedSet = [];
        $values = explode(',', $expression);
        foreach ($values as $value) {
            [$range, $step] = array_pad(explode('/', $value), 2, null);
            [$start, $end] = array_pad(explode('-', $range), 2, null);

            if ($start !== '*' && $end === null && $step === null) {
                $end = $start;
            }

            if ($start === '*') {
                $start = $lowerBound;
            }

            $end ??= $upperBound;
            $step ??= 1;

            if ($resolveAlias) {
                $start = $resolveAlias($start);
                $end = $resolveAlias($end);
            }

            for ($i = $start; $i <= $end; $i += $step) {
                $allowedSet[$i] = true;
            }
        }

        $allowed = array_keys($allowedSet);
        sort($allowed);

        return $allowed;
    }
}
