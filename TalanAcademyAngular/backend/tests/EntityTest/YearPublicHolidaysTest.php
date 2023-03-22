<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 29/03/2019
 * Time: 14:36
 */

namespace App\Entity;

use PHPUnit\Framework\TestCase;

class YearPublicHolidaysTest extends TestCase
{

    public function testStaffCreate()
    {
        $yearHoliday = new YearPublicHolidays();
        $yearHoliday->setDate(new \DateTime('25-07-2019'));
        $holiday = new PublicHolidays();
        $yearHoliday->setHolidays($holiday);


        $this->assertEquals(null,$yearHoliday->getId());
        $this->assertEquals(new \DateTime('25-07-2019'),$yearHoliday->getDate());
        $this->assertInstanceOf(PublicHolidays::class,$yearHoliday->getHolidays());
    }

}
