<?php
use Core\Helpers\DateTime;
use phpmock\phpunit\PHPMock;

/**
 * @covers Core\Helpers\DateTime
 */
class DateTimeTest extends PHPUnit_Framework_TestCase
{
    use PHPMock;

    protected $timezone;
    protected $datetime;
    protected $datetimeUtc;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }


    protected function setUp()
    {
        $this->timezone = 'Europe/Sofia';
        $this->datetime = date('Y-m-d H:i:s');
        $this->datetimeUtc = gmdate('Y-m-d H:i:s');
    }

    /**
     * @covers Core\Helpers\DateTime::getTimezonesList
     */
    public function testGettingTimezonesList()
    {
        $this->assertInternalType('array', DateTime::getTimezonesList());
    }

    /**
     * @covers Core\Helpers\DateTime::setEnvironmentTimezone
     */
    public function testSettingInvalidEnvironmentTimezone()
    {
        /* Suppress PHP error notice of an invalid timezone. */
        $this->assertFalse(@DateTime::setEnvironmentTimezone(strrev($this->timezone)));
    }

    /**
     * @covers Core\Helpers\DateTime::setEnvironmentTimezone
     */
    public function testSettingEmptyEnvironmentTimezone()
    {
        /* Suppress PHP error notice of an invalid timezone. */
        $this->assertTrue(@DateTime::setEnvironmentTimezone(''));
    }

    /**
     * @covers Core\Helpers\DateTime::setEnvironmentTimezone
     */
    public function testSettingEnvironmentTimezone()
    {
        $this->assertTrue(DateTime::setEnvironmentTimezone($this->timezone));
    }

    /**
     * @covers Core\Helpers\DateTime::format
     */
    public function testFormattingDateTime()
    {
        $this->assertEquals($this->datetime, DateTime::format($this->datetimeUtc));
    }

    /**
     * @covers Core\Helpers\DateTime::formatGmt
     */
    public function testFormattingGmtDateTime()
    {
        $this->assertEquals($this->datetimeUtc, DateTime::formatGmt($this->datetime));
    }
}
