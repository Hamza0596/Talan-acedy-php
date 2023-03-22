<?php


namespace App\Service;


use App\Entity\PublicHolidays;
use App\Entity\YearPublicHolidays;
use App\Event\PublicHolidayChangedEvent;
use App\Repository\PublicHolidaysRepository;
use App\Repository\YearPublicHolidaysRepository;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HolidaysService
{
    /**
     * @var PublicHolidaysRepository
     */
    private $publicHolidaysRepository;
    /**
     * @var YearPublicHolidaysRepository
     */
    private $yearPublicHolidaysRepository;

    public function __construct(PublicHolidaysRepository $publicHolidaysRepository, YearPublicHolidaysRepository $yearPublicHolidaysRepository)
    {

        $this->publicHolidaysRepository = $publicHolidaysRepository;
        $this->yearPublicHolidaysRepository = $yearPublicHolidaysRepository;
    }

    public function getWorkingDaysNumber(\DateTime $dateFirst = null, \DateTime $dateSecond = null)
    {
        $daysNumber = 0;
        while ($dateFirst < $dateSecond){
            if ($dateFirst->format('w')== 0 || $dateFirst->format('w')== 6 ){
                $daysNumber++;
            }
            $dateFirst = $dateFirst->modify('+1 day');
        }
        $dateFirst = strtotime($dateFirst->format('Y-m-d'));
        $dateSecond = strtotime($dateSecond->format('Y-m-d'));
        $holidays = $this->yearPublicHolidaysRepository->findAll();
        foreach ($holidays as $holiday) {
            if ($holiday->getDate() == !null) {
                $holidaydate = strtotime($holiday->getDate()->format('Y-m-d'));
                if ($this->checkInRange($dateFirst, $dateSecond, $holidaydate) === true) {
                    $daysNumber++;
                }
            }
        }
        return $daysNumber;
    }

    private function checkInRange($startDate, $endDate, $dateTest)
    {
        return ($dateTest >= $startDate && $dateTest <= $endDate);
    }

    public function calculateEndDate(\DateTime $startDate, $nbDay, $endDateOnly = true)
    {
        $holidaysArray = [];
        $days = [];
        $holidays = $this->yearPublicHolidaysRepository->getDate();

        //convert holidays array object to holidays array date
        foreach ($holidays as $holiday) {
            $holidaysArray[] = $holiday['date']->format('Y-m-d');
        }
        if($nbDay!=0){
            $date = clone $startDate;
            while ($nbDay > 0) {
                $day = date('w', strtotime($date->format('Y-m-d')));
                if ($day != 0 && $day != 6 && !in_array($date->format('Y-m-d'), $holidaysArray)) {
                    $nbDay--;
                    $days[] = clone $date;
                }
                $date = $date->modify('+1 day');
            }


            if ($endDateOnly) {
                return end($days);
            }

            return $days;
        }

        return $startDate;
    }

    public function addHolidaysToNewYear(YearPublicHolidaysRepository $yearPublicHolidaysRepository, ObjectManager $manager){
        $previousHolidays = $yearPublicHolidaysRepository->findHolidaysByPreviousYear();
        if(count($previousHolidays)>0){
            foreach($previousHolidays as $previousHoliday) {
                $dayMonth=date_format($previousHoliday['date'],'d-m');
                $year = date("Y");
                $date = new DateTime(date('d-m-Y h:i',strtotime($dayMonth . '-' . $year)));
                $newHoliday = new YearPublicHolidays();
                $newHoliday->setDate($date);
                $holiday= $manager->getRepository(PublicHolidays::class)->find($previousHoliday['id']);
                $newHoliday->setHolidays($holiday);
                $manager->persist($newHoliday);
                $manager->flush();
            }
        }
        return $previousHolidays;
    }
}
