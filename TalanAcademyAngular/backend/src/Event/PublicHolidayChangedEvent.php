<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 22/05/2019
 * Time: 10:09
 */

namespace App\Event;


use App\Entity\YearPublicHolidays;
use App\Repository\SessionRepository;
use Symfony\Component\EventDispatcher\Event;

class PublicHolidayChangedEvent extends Event
{
    public const NAME = 'public_holiday.changed';

    protected $publicHoliday;

    public function __construct(YearPublicHolidays $publicHoliday)
    {
        $this->publicHoliday = $publicHoliday;
    }

    public function getPublicHoliday()
    {
        return $this->publicHoliday;
    }
}