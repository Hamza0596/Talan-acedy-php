<?php
/**
 * Created by PhpStorm.
 * User: sourajini
 * Date: 15/05/2019
 * Time: 16:17
 */

namespace App\DataFixtures;


use App\Entity\ActivityCourses;
use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Module;
use App\Entity\OrderCourse;
use App\Entity\ProjectSubject;
use App\Entity\Resources;
use App\Entity\StudentReview;
use App\Repository\SessionDayCourseRepository;
use App\Repository\SessionModuleRepository;
use App\Repository\StudentRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AppFixturesTest
 * @package App\DataFixtures
 * @codeCoverageIgnore
 */
class AppFixturesTest extends Fixture implements FixtureGroupInterface
{
    private $faker;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var SessionDayCourseRepository
     */
    private $dayCourseRepository;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * AppFixturesTest constructor.
     * @codeCoverageIgnore
     * @param StudentRepository $studentRepository
     * @param SessionDayCourseRepository $dayCourseRepository
     * @param Filesystem $filesystem
     */
    public function __construct(StudentRepository $studentRepository, SessionDayCourseRepository $dayCourseRepository, Filesystem $filesystem)
    {
        $this->faker = Factory::create();
        $this->studentRepository = $studentRepository;
        $this->dayCourseRepository = $dayCourseRepository;
        $this->filesystem = $filesystem;
    }

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
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function load(ObjectManager $manager)
    {
        $this->loadCursus($manager);
        $this->loadModules($manager);
        $days = $this->loadDays($manager);
        $this->loadOrders($manager);
        $this->loadStudentReview($manager);
        $this->loadCvTestCopie();
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
            for ($j = 0; $j < 6; $j++) {
                $module = new Module();
                $module->setTitle($this->faker->realText(30));
                $module->setRef(uniqid());
                $module->setOrderModule($j + 1);
                $module->setDescription($this->faker->realText(200));
                $module->setCourses($this->getReference("cursus_$i"));
                $this->setReference("module_$i", $module);
                $manager->persist($module);
                if ($j == 1) {
                    $module->setType(Module::PROJECT);
                    for ($k = 0; $k < 3; $k++) {
                        $subject = new ProjectSubject();
                        $subject->setName('subject' . $k);
                        $subject->setProject($module);
                        $subject->setRef('ref_subject' . $k);
                        $manager->persist($subject);
                        $manager->flush();
                    }
                }
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
                    ->setModule($this->getReference("module_$j"))
                    ->setReference('DAY_' . time() . mt_rand())
                    ->setOrdre($i);
                if ($i == 1 || $i == 3 || $i == 5 || $i == 7 || $i == 9 || $i == 18) {
                    $day->setStatus(DayCourse::VALIDATING_DAY);
                } elseif ($i == 2 || $i == 4 || $i == 6 || $i == 8 || $i == 10 || $i == 19) {
                    $day->setStatus(DayCourse::CORRECTION_DAY);
                } else {
                    $day->setStatus(DayCourse::NORMAL_DAY);
                }

                $manager->persist($day);
                $days[] = $day;
            }
        }
        $manager->flush();
        return $days;
    }

    /**
     * Generate StudentReview
     * @codeCoverageIgnore
     * @param ObjectManager $manager
     */
    public function loadStudentReview(ObjectManager $manager)
    {

        $student = $this->studentRepository->findOneBy(['id' => 12]);
        $dayCourse = $this->dayCourseRepository->findOneBy(['id' => 12]);
        for ($i = 0; $i < 5; $i++) {
            $studentReview = new StudentReview();
            $studentReview->setCourse($dayCourse)
                ->setStudent($student)
                ->setRating($this->faker->numberBetween(0, 5))
                ->setComment($this->faker->realText());
            $manager->persist($studentReview);
        }
        $manager->flush();
    }


    public function loadResources(ObjectManager $manager)
    {
        $student = $this->studentRepository->findOneBy(['id' => 10]);
        $dayCourse = $this->dayCourseRepository->findOneBy(['id' => 1]);
        $resource = new Resources();
        $resource->setStatus(Resources::TOAPPROVE);
        $resource->setResourceOwner($student);
        $resource->setDay($dayCourse);
        $resource->setRef('test_ref');
        $resource->setUrl('http://wwww.google.com');
        $resource->setTitle('resource title');
        $manager->persist($resource);
        $manager->flush();
    }

    public function loadOrders(ObjectManager $manager)
    {
        $dayCourse = $this->dayCourseRepository->findOneBy(['id' => 10]);
        $order = new OrderCourse();
        $order->setDescription('test');
        $order->setRef('test');
        $order->setScale(3);
        $order->setDayCourse($dayCourse);
        $manager->persist($order);
        $manager->flush();
    }

    public function loadActivity(ObjectManager $manager)
    {
        $dayCourse = $this->dayCourseRepository->findOneBy(['id' => 10]);
        $order = new ActivityCourses();
        $order->setTitle('test');
        $order->setContent('test');
        $order->setReference('test');
        $order->setDay($dayCourse);
        $manager->persist($order);
        $manager->flush();
    }

    public function loadCvTestCopie()
    {
        $this->filesystem->copy('./public/file_upload/cv-upload-test/cv_test.pdf', './public/file_test/cv_test.pdf');
    }

}
