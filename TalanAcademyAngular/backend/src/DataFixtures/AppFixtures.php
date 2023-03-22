<?php

namespace App\DataFixtures;

use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Module;
use App\Entity\SessionDayCourse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class AppFixtures
 * @package App\DataFixtures
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture implements FixtureGroupInterface
{
    private $faker;

    /**
     * AppFixtures constructor.
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

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
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function load(ObjectManager $manager)
    {
        $this->loadCursus($manager);
        $this->loadModules($manager);
        $this->loadDays($manager);
    }

    /**
     * Generate Cursuses
     *
     * @param ObjectManager $manager
     * @codeCoverageIgnore
     */
    public function loadCursus(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $cursus = new Cursus();
            $cursus->setName($this->faker->realText(30));
            $cursus->setDescription($this->faker->realText(500));
            $this->setReference("cursus_$i", $cursus);
            $manager->persist($cursus);
        }

        $manager->flush();
    }

    /**
     * Generate Modules
     *
     * @param ObjectManager $manager
     * @return array
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function loadModules(ObjectManager $manager)
    {
        $modules = [];
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < random_int(2, 6); $j++) {
                $module = new Module();
                $module->setTitle($this->faker->realText(30));
                $module->setRef(uniqid());
                $module->setOrderModule($j + 1);
                $module->setDescription($this->faker->realText(200));
                $module->setCourses($this->getReference("cursus_$i"));
                $this->setReference("module_$i", $module);
                $manager->persist($module);
                $modules[] = $module;
            }
        }
        $manager->flush();
        return $modules;
    }

    /**
     * Generate Day
     * @codeCoverageIgnore
     * @param ObjectManager $manager
     * @return array
     */
    public function loadDays(ObjectManager $manager)
    {
        $days = [];
        for ($j = 0; $j < 2; $j++) {
            for ($i = 1; $i < 20; $i++) {
                $day = new DayCourse();
                $day->setDescription($this->faker->realText(20))
                    ->setSynopsis($this->faker->realText(30))
                    ->setModule($this->getReference("module_$j"));


                $day->setReference('DAY_' . time() . mt_rand());

                $day->setOrdre($i);
                $state = [DayCourse::VALIDATING_DAY, DayCourse::CORRECTION_DAY, DayCourse::NORMAL_DAY];
                $day->setStatus($state[array_rand($state)]);
                $manager->persist($day);
                $days[] = $day;
            }
        }
        $manager->flush();

        return $days;
    }
}
