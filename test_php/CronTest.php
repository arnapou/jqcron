<?php

declare(strict_types=1);

/*
 * This file is part of the Arnapou jqCron package.
 *
 * (c) Arnaud Buathier <me@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

class CronTest extends TestCase
{
    public function testGetMinutes(): void
    {
        self::assertEquals([10, 15, 20, 25, 30], (new Cron('10-30/5 12 * * *'))->getMinutes());
        self::assertEquals([0, 15, 30, 45], (new Cron('*/15 * * * *'))->getMinutes());
        self::assertEquals([12, 55], (new Cron('55,12 * * * *'))->getMinutes());
        self::assertEquals([0, 10, 15, 20, 23, 30], (new Cron('23,*/30,10-20/5 * * * *'))->getMinutes());
    }

    public function testGetCronHours(): void
    {
        self::assertEquals('12', (new Cron('10-30/5 12 * * *'))->getCronHours());
        self::assertEquals('12-15', (new Cron('* 12,15,14,13 * * *'))->getCronHours());
        self::assertEquals('10,15,20', (new Cron('* 10-20/5 * * *'))->getCronHours());
        self::assertEquals('0,8,16', (new Cron('* */8 * * *'))->getCronHours());
    }

    public function testGetText(): void
    {
        $cron = new Cron('10-30/5 12 * * *');
        self::assertEquals('Chaque jour à 12:10,15,20,25,30', $cron->getText('fr'));
        self::assertEquals('Every day at 12:10,15,20,25,30', $cron->getText('en'));
        self::assertEquals('10,15,20,25,30 12 * * *', $cron->getText('UNKNOWN'));
    }

    public function testGetType(): void
    {
        self::assertEquals(Cron::TYPE_MINUTE, (new Cron('* * * * *'))->getType());
        self::assertEquals(Cron::TYPE_HOUR, (new Cron('1 * * * *'))->getType());
        self::assertEquals(Cron::TYPE_DAY, (new Cron('1 1 * * *'))->getType());
        self::assertEquals(Cron::TYPE_MONTH, (new Cron('* * 1 * *'))->getType());
        self::assertEquals(Cron::TYPE_WEEK, (new Cron('* * * * 1'))->getType());
        self::assertEquals(Cron::TYPE_YEAR, (new Cron('* * * 1 *'))->getType());
        self::assertEquals(Cron::TYPE_UNDEFINED, (new Cron('1 1 1 1 1'))->getType());
    }

    public function testMatchExact(): void
    {
        $cron = new Cron('10-30/5 12 * * *');
        self::assertNotTrue($cron->matchExact(new DateTime('2012-07-01 13:25:10')));
        self::assertTrue($cron->matchExact(new DateTime('2012-07-01 12:15:20')));
    }

    public function testMatchWithMargin(): void
    {
        $cron = new Cron('30 12 * * *');
        self::assertNotTrue($cron->matchWithMargin(new DateTime('2012-07-01 12:32:10'), -1, 5));
        self::assertNotTrue($cron->matchWithMargin(new DateTime('2012-07-01 12:24:10'), -1, 5));
        self::assertTrue($cron->matchWithMargin(new DateTime('2012-07-01 12:33:10'), -3, 5));
        self::assertTrue($cron->matchWithMargin(new DateTime('2012-07-01 12:25:10'), -3, 5));
    }
}
