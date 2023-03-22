<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 03/04/2019
 * Time: 17:21
 */

namespace App\DataFixtures;

use App\Entity\Correction;
use App\Entity\CorrectionResult;
use App\Entity\Cursus;
use App\Entity\SessionDayCourse;
use App\Entity\SessionOrder;
use App\Entity\SessionUserData;
use App\Entity\Staff;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UsertestFixture
 * @package App\DataFixtures
 * @codeCoverageIgnore
 */
class ValidationTestFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $this->loadCorrection($manager);
        $this->loadAdminCorrection($manager);
        $this->loadMentor($manager);

    }

    public function loadCorrection(ObjectManager $manager)
    {
        $corrector = $manager->getRepository(User::class)->find(1);
        $corrected = $manager->getRepository(User::class)->find(2);
        $oder = $manager->getRepository(SessionOrder::class)->find(1);
        $day = $manager->getRepository(SessionDayCourse::class)->find(2);
        $correction = new Correction();
        $correction->setCorrector($corrector);
        $correction->setCorrected($corrected);
        $correction->setDay($day);
        $manager->persist($correction);

        $correctionResult = new CorrectionResult();
        $correctionResult->setCorrection($correction);
        $correctionResult->setOrderCourse($oder);
        $manager->persist($correctionResult);

        $correctionResult2 = new CorrectionResult();
        $correctionResult2->setCorrection($correction);
        $correctionResult2->setOrderCourse($oder);
        $manager->persist($correctionResult2);

        $manager->flush();


    }
    public function loadAdminCorrection(ObjectManager $manager){
        $correction = new Correction();
        $day = $manager->getRepository(SessionDayCourse::class)->find(60);
        $corrector = $manager->getRepository(User::class)->find(2);
        $corrected = $manager->getRepository(User::class)->find(1);

        $correction->setCorrected($corrected);
        $correction->setCorrector($corrector);
        $correction->setDay($day);
        $manager->persist($correction);
        $correctionResult = new CorrectionResult();
        $order = $manager->getRepository(SessionOrder::class)->find(4);
        $correctionResult->setCorrection($correction);
        $correctionResult->setOrderCourse($order);
        $correctionResult->setResult('t');
        $manager->persist($correctionResult);
        $manager->flush();
    }

    public function loadMentor(ObjectManager $manager)
    {
        $mentor2 = new Staff();
        $mentor2->setRoles([User::ROLE_MENTOR]);
        $mentor2->setFunction('mentor');
        $mentor2->setEmail('mentorWithoutSession@talan.com');
        $mentor2->setPassword(password_hash('talan12345', PASSWORD_BCRYPT));
        $mentor2->setFirstName('test mentor');
        $mentor2->setLastName('mentor');
        $mentor2->setToken('a123456aaf');
        $mentor2->setIsActivated(true);
        $cursus = $manager->getRepository(Cursus::class)->find(1);
        $mentor2->setCursus($cursus);
        $manager->persist($mentor2);
        $manager->flush();

    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public static function getGroups(): array
    {
        return ['test'];
    }

}
