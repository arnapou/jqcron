<?php

use PHPUnit\Framework\TestCase;

class CronTest extends TestCase
{

    public function test_getMinutes()
    {
        $this->assertEquals([10, 15, 20, 25, 30], (new Cron('10-30/5 12 * * *'))->getMinutes());
        $this->assertEquals([0, 15, 30, 45], (new Cron('*/15 * * * *'))->getMinutes());
        $this->assertEquals([12, 55], (new Cron('55,12 * * * *'))->getMinutes());
        $this->assertEquals([0, 10, 15, 20, 23, 30], (new Cron('23,*/30,10-20/5 * * * *'))->getMinutes());
    }

    public function test_getCronHours()
    {
        $this->assertEquals("12", (new Cron('10-30/5 12 * * *'))->getCronHours());
        $this->assertEquals("12-15", (new Cron('* 12,15,14,13 * * *'))->getCronHours());
        $this->assertEquals("10,15,20", (new Cron('* 10-20/5 * * *'))->getCronHours());
        $this->assertEquals("0,8,16", (new Cron('* */8 * * *'))->getCronHours());
    }

    public function test_getText()
    {
        $cron = new Cron('10-30/5 12 * * *');
        $this->assertEquals("Chaque jour à 12:10,15,20,25,30", $cron->getText('fr'));
        $this->assertEquals("Every day at 12:10,15,20,25,30", $cron->getText('en'));
        $this->assertEquals("10,15,20,25,30 12 * * *", $cron->getText('UNKNOWN'));
    }

    public function test_getType()
    {
        $this->assertEquals(Cron::TYPE_MINUTE, (new Cron('* * * * *'))->getType());
        $this->assertEquals(Cron::TYPE_HOUR, (new Cron('1 * * * *'))->getType());
        $this->assertEquals(Cron::TYPE_DAY, (new Cron('1 1 * * *'))->getType());
        $this->assertEquals(Cron::TYPE_MONTH, (new Cron('* * 1 * *'))->getType());
        $this->assertEquals(Cron::TYPE_WEEK, (new Cron('* * * * 1'))->getType());
        $this->assertEquals(Cron::TYPE_YEAR, (new Cron('* * * 1 *'))->getType());
        $this->assertEquals(Cron::TYPE_UNDEFINED, (new Cron('1 1 1 1 1'))->getType());
    }

    public function test_matchExact()
    {
        $cron = new Cron('10-30/5 12 * * *');
        $this->assertNotTrue($cron->matchExact(new \DateTime('2012-07-01 13:25:10')));
        $this->assertTrue($cron->matchExact(new \DateTime('2012-07-01 12:15:20')));
    }

    public function test_matchWithMargin()
    {
        $cron = new Cron('30 12 * * *');
        $this->assertNotTrue($cron->matchWithMargin(new \DateTime('2012-07-01 12:32:10'), -1, 5));
        $this->assertNotTrue($cron->matchWithMargin(new \DateTime('2012-07-01 12:24:10'), -1, 5));
        $this->assertTrue($cron->matchWithMargin(new \DateTime('2012-07-01 12:33:10'), -3, 5));
        $this->assertTrue($cron->matchWithMargin(new \DateTime('2012-07-01 12:25:10'), -3, 5));
    }

}
