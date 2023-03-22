<?php

namespace App\DataFixtures;

use App\Entity\Staff;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AdminFixtures
 * @package App\DataFixtures
 * @codeCoverageIgnore
 */
class AdminFixtures extends Fixture implements FixtureGroupInterface
{
    const ADMIN = 'Admin';

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public static function getGroups(): array
    {
        return ['dev'];
    }

    /**
     * @param ObjectManager $manager
     * @return Staff
     * @codeCoverageIgnore
     */
    public function load(ObjectManager $manager)
    {
        $staff = new Staff();
        $staff->setEmail('admin@talan.com');
        $staff->setPassword(password_hash('talan12345', PASSWORD_BCRYPT));
        $staff->setRoles([User::ROLE_ADMIN]);
        $staff->setFirstName(self::ADMIN);
        $staff->setLastName(self::ADMIN);
        $staff->setIsActivated(true);
        $staff->setFunction(self::ADMIN);
        $manager->persist($staff);
        $manager->flush();
        return $staff;
    }
}
