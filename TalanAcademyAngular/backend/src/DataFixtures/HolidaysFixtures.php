<?php


namespace App\DataFixtures;

use App\Entity\PublicHolidays;
use App\Entity\YearPublicHolidays;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class HolidaysFixtures
 * @package App\DataFixtures
 * @codeCoverageIgnore
 */
class HolidaysFixtures extends Fixture implements FixtureGroupInterface
{

    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     * @codeCoverageIgnore
     */
    public static function getGroups(): array
    {
        return ['dev', 'test'];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function load(ObjectManager $manager)
    {
        $this->loadPublicHolidays($manager);
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function loadPublicHolidays(ObjectManager $manager)
    {
        $public_holidays_array = [
            'Nouvel An' => '01-01',
            'fête de la révolution' => '14-01',
            'fête de l\'indépendance' => '20-03',
            'fête des martyrs' => '09-04',
            'fête du travail' => '01-05',
            'fête de la république' => '25-07',
            'fête de la femme' => '13-08',
            'Jour de l’An Hégire' => null,
            'Fête de l’évacuation' => '15-10',
            'La fête du Mouled' => null,
            'Aïd El Fitr' => null,
            'Aïd El Idha' => null,

        ];
        foreach ($public_holidays_array as $label => $time) {
            $yearHoliday = new YearPublicHolidays();
            $holiday = new PublicHolidays();
            if ($time != null) {
                $year = date("Y");
                $yearHoliday->setHolidays($holiday)
                    ->setDate(new \DateTime(date('d-m-Y H:i', strtotime($time . '-' . $year))));
                $manager->persist($yearHoliday);
            }
            $holiday->setLabel($label)
                ->setDate($time);
            $manager->persist($holiday);
        }
        $manager->flush();

    }

}
