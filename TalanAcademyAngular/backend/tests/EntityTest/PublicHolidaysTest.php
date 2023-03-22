<?php

namespace App\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PublicHolidaysTest extends KernelTestCase
{
    private $validator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()
            ->get('validator')->validate(new PublicHolidays());
    }

    public function testPublicHolidays()
    {
        $holiday = new PublicHolidays();
        $holiday->setLabel('Aïd El Fitr')
            ->setDate(Null);

        $this->assertEquals('Aïd El Fitr', $holiday->getLabel());
        $this->assertEquals(Null, $holiday->getDate());
    }

    public function testYearPublicHolidays()
    {
        $holiday = new PublicHolidays();
        $year = new YearPublicHolidays();
        $now = date("Y");
        $holiday->setLabel('Aïd El Fitr')
            ->setDate("01-05");
        $year->setHolidays($holiday)
            ->setDate(new \DateTime(date('d-m-Y H:i', strtotime($holiday->getDate() . '-' . $now))));

        $this->assertEquals('Aïd El Fitr', $holiday->getLabel());
        $this->assertEquals("01-05", $holiday->getDate());
        $this->assertEquals($holiday, $year->getHolidays());
        $this->assertEquals(new \DateTime('2020-05-01 00:00:00'), $year->getDate());
    }
    public function testDeletePublicHolidays()
    {
        $holiday = new PublicHolidays();
        $year = new YearPublicHolidays();
        $holiday->addYearPublicHoliday($year);
        $holiday->removeYearPublicHoliday($year);

        $this->assertEquals(0, $holiday->getYearPublicHolidays()->count());
    }

}
