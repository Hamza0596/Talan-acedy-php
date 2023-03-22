<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 22/05/2019
 * Time: 13:09
 */

namespace App\EventListener;


use App\Entity\Session;
use App\Entity\YearPublicHolidays;
use App\Event\PublicHolidayChangedEvent;
use App\Service\SessionService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PublicHolidaySubscriber implements EventSubscriberInterface
{
    private $manager;
    private $sessionService;
    const FORMAT='Y-m-d';
    const DAYS='days';

    /**
     * PublicHolidaySubscriber constructor.
     * @param ObjectManager $manager
     * @param SessionService $sessionService
     */
    public function __construct(ObjectManager $manager, SessionService $sessionService)
    {
        $this->manager = $manager;
        $this->sessionService = $sessionService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PublicHolidayChangedEvent::NAME => 'onPublicHolidayChange',
        ];
    }


    public function onPublicHolidayChange(PublicHolidayChangedEvent $event)
    {
        $newPublicHolidayDate = $event->getPublicHoliday()->getDate();
        $newPublicHolidayDateStr = date(self::FORMAT, strtotime($newPublicHolidayDate->format(self::FORMAT)));
        $sessions = $this->manager->getRepository(Session::class)->findSessionsWaitingAndInProgress();
        $holidays = $this->manager->getRepository(YearPublicHolidays::class)->getDate();
        foreach ($holidays as $holiday) {
            $holidaysArray[] = $holiday['date']->format(self::FORMAT);
        }
        foreach ($sessions as $session) {
            if ($session->getStartDate() == $newPublicHolidayDate) {
                $nbrJrs=$this->manager->getRepository(Session::class)->countSessionsDays($session);
                $i = 1;
                while (date('w', strtotime($newPublicHolidayDateStr . ' + ' . $i . self::DAYS)) == 0 || date('w', strtotime($newPublicHolidayDateStr . ' + ' . $i . self::DAYS)) == 6 || in_array(date(self::FORMAT, strtotime($newPublicHolidayDateStr . '+' . $i . self::DAYS)), $holidaysArray)) {
                    $i++;
                }
                $date = date(self::FORMAT, strtotime($newPublicHolidayDateStr . '+' . $i . self::DAYS));
                $dateW = date('w', strtotime($newPublicHolidayDateStr . ' + ' . $i . self::DAYS));
                if ($dateW != 0 && $dateW != 6 && !in_array($date, $holidaysArray)) {
                    $session->setStartDate(new \DateTime($date));
                    $this->manager->flush();
                    $this->sessionService->applaySessionEndDate($session, $nbrJrs);
                }
            }
            if ($session->getStartDate() < $newPublicHolidayDate && $session->getEndDate() >= $newPublicHolidayDate) {
                $nbrJrs=$this->manager->getRepository(Session::class)->countSessionsDays($session);
                $this->sessionService->applaySessionEndDate($session, $nbrJrs);
            }
        }
    }


}