<?php

declare(strict_types=1);

/*
 * This file is part of the Arnapou jqCron package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Usage examples :
 * ----------------.
 *
 * $cron = new Cron('10-30/5 12 * * *');
 *
 * var_dump($cron->getMinutes());
 * //  array(5) {
 * //    [0]=> int(10)
 * //    [1]=> int(15)
 * //    [2]=> int(20)
 * //    [3]=> int(25)
 * //    [4]=> int(30)
 * //  }
 *
 * var_dump($cron->getText('fr'));
 * //  string(32) "Chaque jour à 12:10,15,20,25,30"
 *
 * var_dump($cron->getText('en'));
 * //  string(30) "Every day at 12:10,15,20,25,30"
 *
 * var_dump($cron->getType());
 * //  string(3) "day"
 *
 * var_dump($cron->getCronHours());
 * //  string(2) "12"
 *
 * var_dump($cron->matchExact(new \DateTime('2012-07-01 13:25:10')));
 * //  bool(false)
 *
 * var_dump($cron->matchExact(new \DateTime('2012-07-01 12:15:20')));
 * //  bool(true)
 *
 * var_dump($cron->matchWithMargin(new \DateTime('2012-07-01 12:32:50'), -3, 5));
 * //  bool(true)
 */
class Cron
{
    public const TYPE_UNDEFINED = '';
    public const TYPE_MINUTE = 'minute';
    public const TYPE_HOUR = 'hour';
    public const TYPE_DAY = 'day';
    public const TYPE_WEEK = 'week';
    public const TYPE_MONTH = 'month';
    public const TYPE_YEAR = 'year';

    protected array $texts = [
        'fr' => [
            'empty' => '-tout-',
            'name_minute' => 'minute',
            'name_hour' => 'heure',
            'name_day' => 'jour',
            'name_week' => 'semaine',
            'name_month' => 'mois',
            'name_year' => 'année',
            'text_period' => 'Chaque %s',
            'text_mins' => 'à %s minutes',
            'text_time' => 'à %s:%s',
            'text_dow' => 'le %s',
            'text_month' => 'de %s',
            'text_dom' => 'le %s',
            'weekdays' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'],
            'months' => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
        ],
        'en' => [
            'empty' => '-all-',
            'name_minute' => 'minute',
            'name_hour' => 'hour',
            'name_day' => 'day',
            'name_week' => 'week',
            'name_month' => 'month',
            'name_year' => 'year',
            'text_period' => 'Every %s',
            'text_mins' => 'at %s minutes past the hour',
            'text_time' => 'at %s:%s',
            'text_dow' => 'on %s',
            'text_month' => 'of %s',
            'text_dom' => 'on the %s',
            'weekdays' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'months' => ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'],
        ],
    ];

    /** min hour dom month dow. */
    protected string $cron = '';

    protected array $minutes = [];

    protected array $hours = [];

    protected array $months = [];

    /** 0-7 : sunday, monday, ... saturday, sunday. */
    protected array $dow = [];

    protected array $dom = [];

    public function __construct(string $cron = null)
    {
        if (!empty($cron)) {
            $this->setCron($cron);
        }
    }

    public function getCron(): string
    {
        return $this->getCronMinutes() .
            ' ' . $this->getCronHours() .
            ' ' . $this->getCronDaysOfMonth() .
            ' ' . $this->getCronMonths() .
            ' ' . $this->getCronDaysOfWeek();
    }

    /**
     * @param string $lang 'fr' or 'en'
     */
    public function getText(string $lang): string
    {
        // check lang
        if (!isset($this->texts[$lang])) {
            return $this->getCron();
        }
        $texts = $this->texts[$lang];
        // check type
        $type = $this->getType();
        if (self::TYPE_UNDEFINED === $type) {
            return $this->getCron();
        }
        // init
        $elements = [];
        $elements[] = sprintf($texts['text_period'], $texts['name_' . $type]);
        // hour
        if (self::TYPE_HOUR === $type) {
            $elements[] = sprintf($texts['text_mins'], $this->getCronMinutes());
        }
        // week
        if (self::TYPE_WEEK === $type) {
            $dow = $this->getCronDaysOfWeek();
            foreach ($texts['weekdays'] as $i => $wd) {
                $dow = str_replace((string) ($i + 1), $wd, $dow);
            }
            $elements[] = sprintf($texts['text_dow'], $dow);
        }
        // month + year
        if (\in_array($type, [self::TYPE_MONTH, self::TYPE_YEAR], true)) {
            $elements[] = sprintf($texts['text_dom'], $this->getCronDaysOfMonth());
        }
        // year
        if (self::TYPE_YEAR === $type) {
            $months = $this->getCronMonths();
            for ($i = \count($texts['months']) - 1; $i >= 0; --$i) {
                $months = str_replace((string) ($i + 1), $texts['months'][$i], $months);
            }
            $elements[] = sprintf($texts['text_month'], $months);
        }
        // day + week + month + year
        if (\in_array($type, [self::TYPE_DAY, self::TYPE_WEEK, self::TYPE_MONTH, self::TYPE_YEAR], true)) {
            $elements[] = sprintf($texts['text_time'], $this->getCronHours(), $this->getCronMinutes());
        }

        return str_replace('*', $texts['empty'], implode(' ', $elements));
    }

    public function getType(): string
    {
        $mask = (string) preg_replace('/[^* ]/', '-', $this->getCron());
        $mask = (string) preg_replace('/-+/', '-', $mask);
        $mask = (string) preg_replace('/[^-*]/', '', $mask);
        if ('*****' === $mask) {
            return self::TYPE_MINUTE;
        }
        if ('-****' === $mask) {
            return self::TYPE_HOUR;
        }
        if (str_ends_with($mask, '***')) {
            return self::TYPE_DAY;
        }
        if (str_ends_with($mask, '-**')) {
            return self::TYPE_MONTH;
        }
        if (str_ends_with($mask, '**-')) {
            return self::TYPE_WEEK;
        }
        if (str_ends_with($mask, '-*')) {
            return self::TYPE_YEAR;
        }

        return self::TYPE_UNDEFINED;
    }

    public function setCron(string $cron): static
    {
        // sanitize
        $cron = trim($cron);
        $cron = (string) preg_replace('/\s+/', ' ', $cron);
        // explode
        $elements = explode(' ', $cron);
        if (5 !== \count($elements)) {
            throw new InvalidArgumentException('Bad number of elements');
        }
        $this->cron = $cron;
        $this->setMinutes($elements[0]);
        $this->setHours($elements[1]);
        $this->setDaysOfMonth($elements[2]);
        $this->setMonths($elements[3]);
        $this->setDaysOfWeek($elements[4]);

        return $this;
    }

    public function getCronMinutes(): string
    {
        return $this->arrayToCron($this->minutes);
    }

    public function getCronHours(): string
    {
        return $this->arrayToCron($this->hours);
    }

    public function getCronDaysOfMonth(): string
    {
        return $this->arrayToCron($this->dom);
    }

    public function getCronMonths(): string
    {
        return $this->arrayToCron($this->months);
    }

    public function getCronDaysOfWeek(): string
    {
        return $this->arrayToCron($this->dow);
    }

    public function getMinutes(): array
    {
        return $this->minutes;
    }

    public function getHours(): array
    {
        return $this->hours;
    }

    public function getDaysOfMonth(): array
    {
        return $this->dom;
    }

    public function getMonths(): array
    {
        return $this->months;
    }

    public function getDaysOfWeek(): array
    {
        return $this->dow;
    }

    public function setMinutes(array|string $minutes): static
    {
        $this->minutes = $this->cronToArray($minutes, 0, 59);

        return $this;
    }

    /**
     * @return $this
     */
    public function setHours(array|string $hours): static
    {
        $this->hours = $this->cronToArray($hours, 0, 23);

        return $this;
    }

    /**
     * @return $this
     */
    public function setMonths(array|string $months): static
    {
        $this->months = $this->cronToArray($months, 1, 12);

        return $this;
    }

    /**
     * @return $this
     */
    public function setDaysOfWeek(array|string $dow): static
    {
        $this->dow = $this->cronToArray($dow, 0, 7);

        return $this;
    }

    /**
     * @return $this
     */
    public function setDaysOfMonth(array|string $dom): static
    {
        $this->dom = $this->cronToArray($dom, 1, 31);

        return $this;
    }

    protected function parseDate(
        DateTime|DateTimeInterface|int|string $date,
        ?int &$min,
        ?int &$hour,
        ?int &$day,
        ?int &$month,
        ?int &$weekday
    ): DateTime {
        if (is_numeric($date) && (int) $date == $date) {
            $date = new DateTime('@' . $date);
        } elseif (\is_string($date)) {
            $date = new DateTime('@' . strtotime($date));
        }
        if ($date instanceof \DateTime) {
            $min = (int) $date->format('i');
            $hour = (int) $date->format('H');
            $day = (int) $date->format('d');
            $month = (int) $date->format('m');
            $weekday = (int) $date->format('w'); // 0-6

            return new DateTime($date->format('Y-m-d H:i:sP'));
        }
        throw new OutOfBoundsException('Date format not supported');
    }

    public function matchExact(DateTime|DateTimeInterface|int|string $date): bool
    {
        $this->parseDate($date, $min, $hour, $day, $month, $weekday);

        return
            (empty($this->minutes) || \in_array($min, $this->minutes, true))
            && (empty($this->hours) || \in_array($hour, $this->hours, true))
            && (empty($this->dom) || \in_array($day, $this->dom, true))
            && (empty($this->months) || \in_array($month, $this->months, true))
            && (
                empty($this->dow) || \in_array($weekday, $this->dow, true)
                || (0 === $weekday && \in_array(7, $this->dow, true))
                || (7 === $weekday && \in_array(0, $this->dow, true))
            );
    }

    public function matchWithMargin(
        DateTime|DateTimeInterface|int|string $date,
        int $minuteBefore = 0,
        int $minuteAfter = 0
    ): bool {
        if ($minuteBefore > 0) {
            throw new OutOfBoundsException('MinuteBefore parameter cannot be positive !');
        }
        if ($minuteAfter < 0) {
            throw new OutOfBoundsException('MinuteAfter parameter cannot be negative !');
        }
        $date = $this->parseDate($date, $min, $hour, $day, $month, $weekday);
        $oneMinute = new DateInterval('PT1M');
        if ($minuteBefore) {
            $date->sub(new DateInterval('PT' . abs($minuteBefore) . 'M'));
        }
        $n = $minuteAfter + abs($minuteBefore) + 1;
        for ($i = 0; $i < $n; ++$i) {
            if ($this->matchExact($date)) {
                return true;
            }
            $date->add($oneMinute);
        }

        return false;
    }

    protected function arrayToCron(array $array): string
    {
        if (0 === \count($array)) {
            return '*';
        }
        $n = \count($array);
        $cron = [$array[0]];
        $s = $c = $array[0];
        for ($i = 1; $i < $n; ++$i) {
            if ($array[$i] === $c + 1) {
                $c = $array[$i];
                $cron[\count($cron) - 1] = $s . '-' . $c;
            } else {
                $s = $c = $array[$i];
                $cron[] = $c;
            }
        }

        return implode(',', $cron);
    }

    protected function cronToArray(array|string $string, int $min, int $max): array
    {
        $array = [];
        if (\is_array($string)) {
            foreach ($string as $val) {
                if (is_numeric($val) && (int) $val == $val && $val >= $min && $val <= $max) {
                    $array[] = (int) $val;
                }
            }
        } elseif ('*' !== $string) {
            while ('' !== $string) {
                // test "*/n" expression
                if (preg_match('/^\*\/(\d+),?/', $string, $m)) {
                    $imax = min(59, $max);
                    for ($i = max(0, $min); $i <= $imax; $i += $m[1]) {
                        $array[] = (int) $i;
                    }
                    $string = substr($string, \strlen($m[0]));
                    continue;
                }
                // test "a-b/n" expression
                if (preg_match('/^(\d+)-(\d+)\/(\d+),?/', $string, $m)) {
                    $imax = min($m[2], $max);
                    for ($i = max($m[1], $min); $i <= $imax; $i += $m[3]) {
                        $array[] = (int) $i;
                    }
                    $string = substr($string, \strlen($m[0]));
                    continue;
                }
                // test "a-b" expression
                if (preg_match('/^(\d+)-(\d+),?/', $string, $m)) {
                    $imax = min($m[2], $max);
                    for ($i = max($m[1], $min); $i <= $imax; ++$i) {
                        $array[] = (int) $i;
                    }
                    $string = substr($string, \strlen($m[0]));
                    continue;
                }
                // test "c" expression
                if (preg_match('/^(\d+),?/', $string, $m)) {
                    if ($m[1] >= $min && $m[1] <= $max) {
                        $array[] = (int) $m[1];
                    }
                    $string = substr($string, \strlen($m[0]));
                    continue;
                }

                // something goes wrong in the expression
                return [];
            }
        }
        sort($array);

        return $array;
    }
}
