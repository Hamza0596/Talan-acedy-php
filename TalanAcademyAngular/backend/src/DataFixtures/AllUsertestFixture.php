<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 03/04/2019
 * Time: 17:21
 */

namespace App\DataFixtures;


use App\Entity\Cursus;
use App\Entity\Staff;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AllUsertestFixture
 * @package App\DataFixtures
 * @codeCoverageIgnore
 */
class AllUsertestFixture extends Fixture implements FixtureGroupInterface
{

    const MDP = 'talan12345';

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public static function getGroups(): array
    {
        return ['test'];
    }

    /**
     * @param ObjectManager $manager
     * @codeCoverageIgnore
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 5; $i++) {
            $student = new Student();
            $student->setEmail("test.activation.$i@talan.com");
            $student->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
            $student->setRoles([User::ROLE_INSCRIT]);
            $student->setFirstName('test');
            $student->setLastName('test');
            $student->setNewEmail('testActivationemailchange' . $i . '@talan.com');
            if ($i === 1) {
                $student->setNewEmail('mhamdi.wahid@gmail.com');
                $student->setTel('53875208');
                $student->setIsActivated(true);
                $student->setImage('a35daad7aca921bab7a3dbb32a0ec01a.jpeg');
            }
            if ($i === 3) {
                $student->setToken('testTokenResetPasswordInactiveAccount');
                $student->setIsActivated(false);
            } else if ($i === 4) {
                $student->setToken('testTokenResetPasswordActiveAccount');
                $student->setIsActivated(true);
            } else {
                $student->setToken('testTokenActivation');
                $student->setIsActivated(false);
            }

            $manager->persist($student);
        }

        $student = new Student();
        $student->setToken('testTokenActivationValidAndSendMail');
        $student->setEmail("slim.arfaoui@talan.com");
        $student->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
        $student->setRoles([User::ROLE_INSCRIT]);
        $student->setFirstName('test');
        $student->setLastName('test');


        $student2 = new Student();
        $student2->setEmail('active.account@talan.com');
        $student2->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
        $student2->setRoles([User::ROLE_INSCRIT]);
        $student2->setFirstName('test');
        $student2->setLastName('test');
        $student2->setToken('');
        $student2->setIsActivated(true);
        $manager->persist($student2);


        $cursus = new Cursus();
        $cursus->setDescription('description cursus');
        $cursus->setName('PHP/SYMFONY');
        $cursus->setVisibility(Cursus::VISIBLE);
        $cursus->setImage('test_cursus.jpeg');
        $manager->persist($cursus);
        $manager->flush();


        $mentor1 = new Staff();
        $mentor1->setRoles([User::ROLE_MENTOR]);
        $mentor1->setFunction('mentor');
        $mentor1->setEmail('mentor1@talan.com');
        $mentor1->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
        $mentor1->setFirstName('mentor');
        $mentor1->setLastName('mentor');
        $mentor1->setToken('a123456aaf');
        $mentor1->setIsActivated(true);
        $mentor1->setCursus($cursus);
        $manager->persist($mentor1);
        $manager->flush();


    }

}
