<?php
/**
 * Created by PhpStorm.
 * User: jghada
 * Date: 16/05/2019
 * Time: 10:48
 */

namespace App\DataFixtures;


use App\Entity\Affectation;
use App\Entity\Candidature;
use App\Entity\CandidatureState;
use App\Entity\Correction;
use App\Entity\CorrectionResult;
use App\Entity\Cursus;
use App\Entity\DayCourse;
use App\Entity\Preparcours;
use App\Entity\Session;
use App\Entity\SessionDayCourse;
use App\Entity\SessionMentor;
use App\Entity\SessionModule;
use App\Entity\SessionOrder;
use App\Entity\SessionProjectSubject;
use App\Entity\SessionUserData;
use App\Entity\Staff;
use App\Entity\Student;
use App\Entity\SubmissionWorks;
use App\Entity\User;
use App\Repository\SessionModuleRepository;
use App\Service\AssociateDateService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class SessionFixtures
 * @package App\DataFixtures
 * @codeCoverageIgnore
 */
class SessionFixtures extends Fixture implements FixtureGroupInterface
{
    const MDP = 'talan12345';
    private $faker;
    /**
     * @var AssociateDateService
     */
    private $associateDateService;
    /**
     * @var SessionModuleRepository
     */
    private $moduleRepository;

    /**
     * SessionFixtures constructor.
     * @codeCoverageIgnore
     * @param AssociateDateService $associateDateService
     * @param SessionModuleRepository $moduleRepository
     */
    public function __construct(AssociateDateService $associateDateService, SessionModuleRepository $moduleRepository)
    {
        $this->faker = Factory::create();
        $this->associateDateService = $associateDateService;
        $this->moduleRepository = $moduleRepository;
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
        $this->loadSession($manager);
        $this->loadModules($manager);
        $this->loadDays($manager);
        $this->loadSessionCorrection($manager);
        $this->loadSessionJokerRetrait($manager);
        $this->loadSessionUserData($manager);
        $this->loadSubjectsSessionProjectList($manager);
        $this->loadProjects($manager);
        $this->loadAffectation($manager);
        $this->loadSubjectProjectForMentor($manager);
        $this->loadPreparcours($manager);
        $this->loadConfirmedApprentice($manager);
    }

    /**
     * Generate Sessions
     *
     * @param ObjectManager $manager
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function loadSession(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $session = new Session();
            $cursus = new Cursus();
            $cursus->setVisibility('visible');
            $cursus->setName('test');
            $cursus->setDescription('test');
            $manager->persist($cursus);
            $manager->flush();
            $session->setOrdre($i + 1);
            if ($i == 0) {
                $session->setStartDate((new \DateTime())->modify('-10 day'));
                $session->setEndDate(new \DateTime('tomorrow'));
            } elseif ($i == 1) {
                $session->setStartDate(new \DateTime('tomorrow'));
                $session->setEndDate(new \DateTime('tomorrow'));
            } elseif ($i == 9) {
                $session->setStartDate((new \DateTime())->modify('-15 day'));
                $session->setEndDate((new \DateTime())->modify('-5 day'));
                $student3 = new Student();
                $student3->setEmail("apprentiPassedSession@talan.com");
                $student3->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $student3->setRoles([User::ROLE_APPRENTI]);
                $student3->setToken('testTokenActivation');
                $student3->setFirstName('test apprenti');
                $student3->setLastName('test');
                $student3->setTel('53875208');
                $student3->setIsActivated(true);
                $student3->setImage('a35daad7aca921bab7a3dbb32a0ec01a.jpeg');
                $session->setCursus($cursus);
                $sessionUser = new SessionUserData();
                $sessionUser->setUser($student3);
                $sessionUser->setStatus(SessionUserData::QUALIFIED);

            } else {
                $session->setStartDate(new \DateTime('now'));
                $session->setEndDate(new \DateTime('tomorrow'));
            }
            $session->setStatus('en attente');
            $session->setMoy(1);
            $session->setJokerNbr(3);
            $session->setHMaxCorection(12);
            $session->setHMaxSubmit(12);
            $session->setNbrValidation(1);
            $session->setPercentageOrder(50);
            $session->setCursus($cursus);
            $manager->persist($session);
            $manager->flush();

            if ($i == 0) {
                $mentor = new Staff();
                $mentor->setRoles([User::ROLE_MENTOR]);
                $mentor->setFunction('mentor');
                $mentor->setEmail('mentorr@talan.com');
                $mentor->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $mentor->setFirstName('mentor');
                $mentor->setLastName('mentor');
                $mentor->setToken('a1234');
                $mentor->setIsActivated(false);
                $mentor->setCursus($cursus);
                $manager->persist($mentor);
                $manager->flush();
                $sessionMentor = new SessionMentor();
                $sessionMentor->setMentor($mentor);
                $sessionMentor->setSession($session);
                $sessionMentor->setStatus(SessionMentor::ACTIVE);
                $manager->persist($sessionMentor);

                $student2 = new Student();
                $student2->setEmail("apprenti@talan.com");
                $student2->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $student2->setRoles([User::ROLE_APPRENTI]);
                $student2->setToken('testTokenActivation');
                $student2->setFirstName('test apprenti');
                $student2->setLastName('test');
                $student2->setTel('53875208');
                $student2->setIsActivated(true);
                $student2->setImage('a35daad7aca921bab7a3dbb32a0ec01a.jpeg');
                $sessionUser = new SessionUserData();
                $sessionUser->setUser($student2);
                $sessionUser->setSession($session);
                $sessionUser->setNbrJoker(3);
                $sessionUser->setMission(false);
                $sessionUser->setStatus(SessionUserData::QUALIFIED);
                $manager->persist($sessionUser);
                $manager->persist($student2);
                $manager->flush();


                $apprenti1 = new Student();
                $apprenti1->setEmail("apprenti1@talan.com");
                $apprenti1->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $apprenti1->setRoles([User::ROLE_APPRENTI]);
                $apprenti1->setToken('testTokenActivation');
                $apprenti1->setFirstName('test apprenti');
                $apprenti1->setLastName('test');
                $apprenti1->setTel('53875208');
                $apprenti1->setIsActivated(true);
                $apprenti1->setImage('a35daad7aca921bab7a3dbb32a0ec01a.jpeg');
                $sessionUser = new SessionUserData();
                $sessionUser->setUser($apprenti1);
                $sessionUser->setSession($session);
                $sessionUser->setNbrJoker(3);
                $sessionUser->setMission(false);
                $sessionUser->setStatus(Student::APPRENTI);
                $manager->persist($sessionUser);
                $manager->persist($apprenti1);
                $manager->flush();

                $mentor = new Staff();
                $mentor->setRoles([User::ROLE_MENTOR]);
                $mentor->setFunction('mentor');
                $mentor->setEmail('mentor@talan.com');
                $mentor->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $mentor->setFirstName('mentor');
                $mentor->setLastName('mentor');
                $mentor->setToken('a1234');
                $mentor->setIsActivated(false);
                $mentor->setCursus($cursus);
                $manager->persist($mentor);
                $manager->flush();


                $candidate = new Student();
                $candidate->setEmail('candidate.account@talan.com');
                $candidate->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $candidate->setRoles([User::ROLE_CANDIDAT]);
                $candidate->setFirstName('test');
                $candidate->setLastName('test');
                $candidate->setToken('');
                $candidate->setImage('test_image.jpg');
                $candidate->setIsActivated(true);
                $manager->persist($candidate);
                $candidature = new Candidature();
                $candidature->setCandidat($candidate);
                $candidature->setStatus('nouveau');
                $candidature->setCursus($cursus);
                $candidature->setCv('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_test' . DIRECTORY_SEPARATOR . 'cv_test.pdf');
                $candidature->setLinkLinkedin('https://www.linkedin.com/in/whm');
                $candidature->setDegree("diplome d'ingenieur");
                $candidature->setGrades('Bac  5');

                $cursus = new Cursus();
                $cursus->setName('test');
                $cursus->setVisibility('visible');
                $cursus->setDescription('test');
                $candidature->setCursus($cursus);
                $manager->persist($cursus);
                $candidature->setDatePostule(new \DateTime());
                $manager->persist($candidature);
                $manager->flush();


                $student1 = new Student();
                $student1->setEmail("test.activationprofile.1@talan.com");
                $student1->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $student1->setRoles([User::ROLE_INSCRIT]);
                $student1->setToken('testTokenActivation');
                $student1->setFirstName('test');
                $student1->setLastName('test');
                $student1->setNewEmail('testActivationemailchange10@talan.com');
                $student1->setNewEmail('mhamdi.wahid@gmail.com');
                $student1->setTel('53875208');
                $student1->setIsActivated(true);
                $student1->setImage('test_image.jpg');
                $manager->persist($student1);
                $manager->flush();


                $candidatureState = new CandidatureState();
                $candidatureState->setCandidature($candidature);
                $candidatureState->setStatus('nouveau');
                $candidatureState->setTitle('test');
                $candidatureState->setDescription('test');
                $candidatureState->setDate(new \DateTime());
                $manager->persist($candidatureState);

                $candidate = new Student();
                $candidate->setEmail('candidate.account2@talan.com');
                $candidate->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $candidate->setRoles([User::ROLE_CANDIDAT]);
                $candidate->setFirstName('test');
                $candidate->setLastName('test');
                $candidate->setToken('');
                $candidate->setImage('test_image.jpg');
                $candidate->setIsActivated(true);
                $manager->persist($candidate);
                $candidature = new Candidature();
                $candidature->setCandidat($candidate);
                $candidature->setStatus('nouveau');
                $cursus = new Cursus();
                $cursus->setName('test');
                $cursus->setVisibility('visible');
                $cursus->setDescription('test');
                $candidature->setCursus($cursus);
                $manager->persist($cursus);
                $candidature->setDatePostule(new \DateTime());
                $manager->persist($candidature);
                $candidatureState = new CandidatureState();
                $candidatureState->setCandidature($candidature);
                $candidatureState->setStatus('nouveau');
                $candidatureState->setTitle('test');
                $candidatureState->setDescription('test');
                $candidatureState->setDate(new \DateTime());
                $manager->persist($candidatureState);

                $newCandidate = new Student();
                $newCandidate->setEmail('candidate.account3@talan.com');
                $newCandidate->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $newCandidate->setRoles([User::ROLE_CANDIDAT]);
                $newCandidate->setFirstName('test');
                $newCandidate->setLastName('test');
                $newCandidate->setToken('');
                $newCandidate->setImage('test_image.jpg');
                $newCandidate->setIsActivated(true);
                $manager->persist($newCandidate);

                $newCandidature = new Candidature();
                $newCandidature->setCursus($cursus);
                $newCandidature->setCandidat($newCandidate);
                $newCandidature->setDatePostule(new \DateTime());
                $newCandidature->setStatus(Candidature::NOUVEAU);
                $manager->persist($newCandidature);
                $manager->flush();

                $interviewCandidature = new Candidature();
                $interviewCandidature->setCursus($cursus);
                $interviewCandidature->setCandidat($newCandidate);
                $interviewCandidature->setDatePostule(new \DateTime());
                $interviewCandidature->setStatus(Candidature::INVITE_ENTRETIEN);
                $manager->persist($interviewCandidature);
                $manager->flush();

                $acceptedCandidature = new Candidature();
                $acceptedCandidature->setCursus($cursus);
                $acceptedCandidature->setCandidat($newCandidate);
                $acceptedCandidature->setDatePostule(new \DateTime());
                $acceptedCandidature->setStatus(Candidature::ACCEPTE);
                $sessionUser = new SessionUserData();
                $sessionUser->setSession($session);
                $sessionUser->setUser($newCandidate);
                $sessionUser->setCandidature($acceptedCandidature);
                $sessionUser->setMission(true);
                $sessionUser->setStatus(SessionUserData::ELIMINATED);
                $sessionUser->setNbrJoker(2);
                $manager->persist($sessionUser);
                $manager->persist($acceptedCandidature);
                $manager->flush();


            }
            if ($i == 9) {
                $candidatureStudent3 = new Candidature();
                $candidatureStudent3->setCandidat($student3);
                $candidatureStudent3->setStatus(Candidature::ACCEPTE);
                $candidatureStudent3->setCursus($cursus);
                $candidatureStudent3->setDatePostule(new \DateTime('now'));
                $manager->persist($candidatureStudent3);
                $sessionUser->setSession($session);
                $sessionUser->setCandidature($candidatureStudent3);
                $sessionUser->setNbrJoker(3);
                $sessionUser->setMission(false);
                $sessionUser->setStatus(SessionUserData::CONFIRMED);
                $manager->persist($sessionUser);
                $manager->persist($student3);
                $manager->flush();
            }


            $this->setReference("session_$i", $session);
        }


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
        for ($i = 0; $i < 9; $i++) {
            for ($j = 0; $j < 4; $j++) {
                $module = new SessionModule();
                $module->setTitle($this->faker->realText(30));
                $module->setRef(uniqid());
                if ($j===0)
                {
                $module->setType('PROJECT');
                }else {
                $module->setType('MODULE');
                }
                $module->setDescription($this->faker->realText(200));
                $module->setOrderModule($j + 1);
                $module->setSession($this->getReference("session_$i"));
                $this->setReference("module_$i", $module);
                $manager->persist($module);
                $modules[] = $module;
            }
            for ($k = 1; $k < 3; $k++) {
                $day = new SessionDayCourse();
                $day->setDescription($this->faker->realText(20))
                    ->setSynopsis($this->faker->realText(30))
                    ->setModule($module)
                    ->setReference('DAY_' . time() . mt_rand())
                    ->setOrdre($k);
                $day->setStatus(SessionDayCourse::NORMAL_DAY);
                $manager->persist($day);
            }
        }
        $manager->flush();
        return $modules;
    }

    /**
     * Generate Day
     * @param ObjectManager $manager
     * @return array
     * @codeCoverageIgnore
     */
    public function loadDays(ObjectManager $manager)
    {
        $days = [];
        $k = 0;
        for ($j = 0; $j < 2; $j++) {
            for ($i = 1; $i < 20; $i++) {
                $module = $manager->getRepository(SessionModule::class)->find($j + 1);
                $day = new SessionDayCourse();
                $day->setDescription($this->faker->realText(20))
                    ->setSynopsis($this->faker->realText(30))
                    ->setModule($module)
                    ->setReference('DAY_' . time() . mt_rand())
                    ->setOrdre($i);
                $state = [SessionDayCourse::NORMAL_DAY, SessionDayCourse::VALIDATING_DAY, SessionDayCourse::CORRECTION_DAY];
                $day->setStatus($state[$k]);
                $k++;
                if ($k > 2) {
                    $k = 0;
                }


                $manager->persist($day);
                $days[] = $day;
                if ($i == 1) {
                    $order = new SessionOrder();
                    $order->setRef('test');
                    $order->setDescription('test');
                    $order->setScale(2);
                    $order->setDayCourse($day);
                    $manager->persist($order);
                }
            }
        }
        $manager->flush();
        return $days;
    }

    public function loadSessionCorrection(ObjectManager $manager)
    {
        $cursus = $manager->getRepository(Cursus::class)->find(1);
        $module = new SessionModule();
        $module->setTitle('testCorrection');
        $module->setRef('testCorrection');
        $module->setDescription('testCorrection');
        $module->setOrderModule(1);
        $manager->persist($module);

        $validatingDay = new SessionDayCourse();
        $validatingDay->setReference('test');
        $validatingDay->setDescription('test');
        $validatingDay->setOrdre(1);
        $validatingDay->setModule($module);
        $validatingDay->setStatus(DayCourse::VALIDATING_DAY);
        $manager->persist($validatingDay);

        $order = new SessionOrder();
        $order->setDayCourse($validatingDay);
        $order->setScale(4);
        $order->setRef('test');
        $order->setDescription('test');
        $manager->persist($order);

        $correctionDay = new SessionDayCourse();
        $correctionDay->setReference('test');
        $correctionDay->setDescription('test');
        $correctionDay->setOrdre(2);
        $correctionDay->setModule($module);
        $correctionDay->setStatus(DayCourse::CORRECTION_DAY);
        $manager->persist($correctionDay);

        $session = new Session();

        $session->addModule($module);
        if (date('N', (new \DateTime())->format('U')) == 1) {
            $session->setStartDate((new \DateTime())->modify('-3 day'));
            $session->setEndDate(new \DateTime('today'));

        } else {
            $session->setStartDate((new \DateTime())->modify('-1 day'));
            $session->setEndDate(new \DateTime('today'));
        }


        $session->setStatus('en cours');
        $session->setMoy(1);
        $session->setJokerNbr(3);
        $session->setHMaxCorection(23);
        $session->setHMaxSubmit(23);
        $session->setNbrValidation(1);
        $session->setPercentageOrder(50);
        $session->setCursus($cursus);
        $manager->persist($session);

        for ($i = 1; $i <= 2; $i++) {
            $student = new Student();
            $student->setEmail("apprentiCorrection" . $i . "@talan.com");
            $student->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
            $student->setRoles([User::ROLE_APPRENTI]);
            $student->setToken('testTokenActivation');
            $student->setFirstName('test apprenti');
            $student->setLastName('test');
            $student->setTel('53875208');
            $student->setIsActivated(true);
            $student->setImage('a35daad7aca921bab7a3dbb32a0ec01a.jpeg');
            $manager->persist($student);

            $candidatureStudent = new Candidature();
            $candidatureStudent->setStatus(Candidature::ACCEPTE);
            $candidatureStudent->setCandidat($student);
            $candidatureStudent->setDatePostule(new \DateTime('now'));
            $candidatureStudent->setCursus($cursus);
            $manager->persist($candidatureStudent);
            $sessionUser = new SessionUserData();
            $sessionUser->setCandidature($candidatureStudent);
            $sessionUser->setUser($student);
            $sessionUser->setSession($session);
            $sessionUser->setNbrJoker(3);
            $sessionUser->setMission(false);
            $sessionUser->setStatus(SessionUserData::QUALIFIED);
            $manager->persist($sessionUser);

            $submissionWork = new SubmissionWorks();
            $submissionWork->setCourse($validatingDay);
            $submissionWork->setStudent($student);
            $submissionWork->setRepoLink('https://www.google.com/');
            $manager->persist($submissionWork);
        }

        $manager->flush();
    }


    public function loadSessionJokerRetrait(ObjectManager $manager)
    {
        for ($i = 0; $i < 6; $i++) {
            $cursus = $manager->getRepository(Cursus::class)->find(1);
            $module = new SessionModule();
            $module->setTitle('testJoker');
            $module->setRef('testJoker');
            $module->setDescription('testJoker');
            $module->setOrderModule(1);
            $manager->persist($module);

            $validatingDay = new SessionDayCourse();
            $validatingDay->setReference('testJoker');
            $validatingDay->setDescription('testJoker');
            $validatingDay->setOrdre(1);
            $validatingDay->setModule($module);
            $validatingDay->setStatus(DayCourse::VALIDATING_DAY);
            $manager->persist($validatingDay);

            $order = new SessionOrder();
            $order->setDayCourse($validatingDay);
            $order->setScale(4);
            $order->setRef('test');
            $order->setDescription('test');
            $manager->persist($order);

            $correctionDay = new SessionDayCourse();
            $correctionDay->setReference('test');
            $correctionDay->setDescription('test');
            $correctionDay->setOrdre(2);
            $correctionDay->setModule($module);
            $correctionDay->setStatus(DayCourse::CORRECTION_DAY);
            $manager->persist($correctionDay);

            $session = new Session();
            $session->addModule($module);
            $session->setHMaxCorection(12);
            $session->setHMaxSubmit(23);

            if (date('N', (new \DateTime())->format('U')) == 1) {
                $session->setStartDate((new \DateTime())->modify('-3 day'));
                $session->setEndDate(new \DateTime('today'));

            } else {
                $session->setStartDate((new \DateTime())->modify('-1 day'));
                $session->setEndDate(new \DateTime('today'));
            }

            if ($i == 0) {
                if (date('N', (new \DateTime())->format('U')) == 1) {
                    $session->setStartDate((new \DateTime())->modify('-2 day'));
                    $session->setEndDate(new \DateTime('tomorrow'));

                } else {
                    $session->setStartDate(new \DateTime('today'));
                    $session->setEndDate(new \DateTime('tomorrow'));
                }
                $session->setHMaxSubmit(1);

            } elseif ($i == 1) {
                $session->setHMaxCorection(1);
            }

            $session->setStatus('en cours');
            $session->setMoy(1);
            $session->setJokerNbr(3);
            $session->setNbrValidation(1);
            $session->setPercentageOrder(50);
            $session->setCursus($cursus);
            $manager->persist($session);

            for ($j = 1; $j <= 2; $j++) {
                $student = new Student();
                $student->setEmail("apprentiCorrection" . $i . $j . "@talan.com");
                $student->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
                $student->setRoles([User::ROLE_APPRENTI]);
                $student->setToken('testTokenActivation');
                $student->setFirstName('test apprenti');
                $student->setLastName('test');
                $student->setTel('53875208');
                $student->setIsActivated(true);
                $student->setImage('a35daad7aca921bab7a3dbb32a0ec01a.jpeg');
                $manager->persist($student);

                $sessionUser = new SessionUserData();
                $sessionUser->setUser($student);
                $sessionUser->setSession($session);
                $sessionUser->setNbrJoker(3);
                $sessionUser->setMission(false);
                $manager->persist($sessionUser);
                if ($i != 0) {
                    $submissionWork = new SubmissionWorks();
                    $submissionWork->setCourse($validatingDay);
                    $submissionWork->setStudent($student);
                    $submissionWork->setRepoLink('https://www.google.com/');
                    $manager->persist($submissionWork);
                }

            }
            $manager->flush();
        }
    }

    public function loadSessionUserData(ObjectManager $manager)
    {


        $sessionUser = $manager->getRepository(SessionUserData::class)->find(1);
        $sessionUser2 = $manager->getRepository(SessionUserData::class)->find(2);
        $apprenti = $manager->getRepository(User::class)->find(10);
        $cursus = $manager->getRepository(Cursus::class)->find(1);
        $acceptedCandidature = new Candidature();
        $acceptedCandidature->setCandidat($apprenti);
        $acceptedCandidature->setCursus($cursus);
        $acceptedCandidature->setDatePostule(new \DateTime());
        $acceptedCandidature->setStatus(Candidature::ACCEPTE);
        $acceptedCandidature->setCv('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_test' . DIRECTORY_SEPARATOR . 'cv_test.pdf');
        $acceptedCandidature->setLinkLinkedin('https://www.linkedin.com/in/whm');
        $acceptedCandidature->setDegree("diplome d'ingenieur");
        $acceptedCandidature->setGrades('Bac  5');


        $manager->persist($acceptedCandidature);
        $sessionUser->setCandidature($acceptedCandidature);
        $sessionUser->setProfilSlack(base64_encode('apprenti'));
        $sessionUser->setRepoGit(base64_encode('apprenti'));
        //sessionUser pour affectation//

        $session1= $manager->getRepository(Session::class)->find(8);
        $apprenti = $manager->getRepository(Student::class)->find(11);
        $sessionUserAffectation = new SessionUserData();
        $sessionUserAffectation->setSession($session1);
        $sessionUserAffectation->setUser($apprenti);
        $acceptedCandidature3 = new Candidature();
        $acceptedCandidature3->setCandidat($apprenti);
        $acceptedCandidature3->setCursus($cursus);
        $acceptedCandidature3->setDatePostule(new \DateTime());
        $acceptedCandidature3->setStatus(Candidature::ACCEPTE);
        $acceptedCandidature3->setCv('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_test' . DIRECTORY_SEPARATOR . 'cv_test.pdf');
        $acceptedCandidature3->setLinkLinkedin('https://www.linkedin.com/in/whm');
        $acceptedCandidature3->setDegree("diplome d'ingenieur");
        $acceptedCandidature3->setGrades('Bac  5');
        $manager->persist($acceptedCandidature3);
        $sessionUserAffectation->setCandidature($acceptedCandidature3);
        $sessionUserAffectation->setProfilSlack(base64_encode('apprenti'));
        $sessionUserAffectation->setRepoGit(base64_encode('apprenti'));
        $manager->persist($sessionUserAffectation);

        /******/

        $acceptedCandidature2 = new Candidature();
        $acceptedCandidature2->setCandidat($apprenti);
        $acceptedCandidature2->setCursus($cursus);
        $acceptedCandidature2->setDatePostule(new \DateTime());
        $acceptedCandidature2->setStatus(Candidature::ACCEPTE);
        $acceptedCandidature2->setCv('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_test' . DIRECTORY_SEPARATOR . 'cv_test.pdf');
        $acceptedCandidature2->setLinkLinkedin('https://www.linkedin.com/in/whm');
        $acceptedCandidature2->setDegree("diplome d'ingenieur");
        $acceptedCandidature2->setGrades('Bac  5');
        $manager->persist($acceptedCandidature2);
        $sessionUser2->setCandidature($acceptedCandidature2);
        $manager->persist($sessionUser);
        $manager->persist($sessionUser2);

        $candidature = $manager->getRepository(Candidature::class)->find(4);
        $candidatureStateinterview = new CandidatureState();
        $candidatureStateinterview->setCandidature($candidature);
        $candidatureStateinterview->setStatus(Candidature::INVITE_ENTRETIEN);
        $candidatureStateinterview->setTitle('test-interview');
        $candidatureStateinterview->setDescription('testinterview');
        $candidatureStateinterview->setDate(new \DateTime());
        $manager->persist($candidatureStateinterview);
        $manager->flush();

        $session = $manager->getRepository(Session::class)->find(1);
        $currentDay = $this->associateDateService->getCurrentDayAndPreviousDay($session)['currentDay'];
        $day = $manager->getRepository(SessionDayCourse::class)->find($currentDay);
        $day->setReference('DAY-RESOURCE');
        $manager->persist($day);
        $day1 = $manager->getRepository(DayCourse::class)->find($currentDay);
        $day1->setReference('DAY-RESOURCE');
        $manager->persist($day1);
        $manager->flush();


    }

    /**
     * Generate ProjectList
     * @codeCoverageIgnore
     * @param ObjectManager $manager
     */
    public function loadSubjectsSessionProjectList(ObjectManager $manager)
    {
        $moduleRepository = $this->moduleRepository->findOneBy(['id' => 32]);
        for ($i = 0; $i < 5; $i++) {
            $subject = new SessionProjectSubject();
            $subject->setStatus(SessionProjectSubject::DEACTIVATED)
                ->setRef("subject_" . time() . "_" . mt_rand())->setName($this->faker->sentence(3))
                ->setSpecification($this->faker->realText())
                ->setSessionProject($moduleRepository);
            $manager->persist($subject);
        }
        for ($i = 0; $i < 5; $i++) {
            $subject = new SessionProjectSubject();
            $subject->setStatus(SessionProjectSubject::ACTIVATED)
                ->setRef("subject_" . time() . "_" . mt_rand())->setName($this->faker->sentence(3))
                ->setSpecification($this->faker->realText())->setSessionProject($moduleRepository);
            $manager->persist($subject);
        }
        $manager->flush();
    }

    public function loadSubjectProjectForMentor(ObjectManager $manager)
    {
        $session = $manager->getRepository(Session::class)->find(1);
        $module = new SessionModule();
        $module->setSession($session);
        $module->setTitle('test');
        $module->setRef('testJoker');
        $module->setDescription('testJoker');
        $module->setOrderModule(1);
        $module->setType('PROJECT');
        $module->setDuration(8);
        $manager->persist($module);

        $subject = new SessionProjectSubject();
        $subject->setSessionProject($module);
        $subject->setStatus(SessionProjectSubject::ACTIVATED);
        $subject->setName('test project');
        $subject->setRef("subject_" . time() . "_" . mt_rand());
        $manager->persist($subject);

        $manager->flush();
    }

    public function loadProjects(ObjectManager $manager)
    {
        $module = $this->moduleRepository->findOneBy(['id' => 32]);
        $module->setType('PROJECT');
        $module->setDuration(8);
        $manager->persist($module);
        $manager->flush();
    }

    public function loadAffectation(ObjectManager $manager)
    {
        $apprenti = $manager->getRepository(Student::class)->find(11);
        $subject = $manager->getRepository(SessionProjectSubject::class)->find(3);
        $affectation = new Affectation();
        $affectation->setSubject($subject);
        $affectation->setStudent($apprenti);
        $apprentiAffected2 = $manager->getRepository(Student::class)->find(11);
        $subject1 = $manager->getRepository(SessionProjectSubject::class)->find(2);
        $affectation1 = new Affectation();
        $affectation1->setSubject($subject1);
        $affectation1->setStudent($apprentiAffected2);
        $manager->persist($affectation);
        $manager->persist($affectation1);
        $manager->flush();
    }

    public function loadPreparcours(ObjectManager $manager){
        $preparcours = new Preparcours();
        $preparcours->setDescription('test Description');
        $preparcours->setPdf('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_test' . DIRECTORY_SEPARATOR . 'cv_test.pdf');
        $preparcours->setIsActivated(1);
        $preparcours->setDateCreation(new \DateTime());
        $manager->persist($preparcours);

        $candidate = $manager->getRepository(Student::class)->find(14);
        $candidature = new Candidature();
        $candidature->setCandidat($candidate);
        $candidature->setStatus('nouveau');
        $candidature->setDatePostule(new \DateTime());
        $candidature->setCv('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_test' . DIRECTORY_SEPARATOR . 'cv_test.pdf');
        $candidature->setLinkLinkedin('https://www.linkedin.com/in/whm');
        $candidature->setDegree("diplome d'ingenieur");
        $candidature->setGrades('Bac  5');
        $cursus = $manager->getRepository(Cursus::class)->find(1);
        $candidature->setCursus($cursus);
        $manager->persist($candidature);
          $manager->flush();

    }

    public function loadConfirmedApprentice(ObjectManager $manager)
    {

        $student3 = new Student();
        $student3->setEmail("apprentiMentor@talan.com");
        $student3->setPassword(password_hash(self::MDP, PASSWORD_BCRYPT));
        $student3->setRoles([User::ROLE_APPRENTI]);
        $student3->setToken('testTokenActivation');
        $student3->setFirstName('test apprenti to be mentor');
        $student3->setLastName('to be mentor');
        $student3->setTel('53875208');
        $student3->setIsActivated(true);
        $student3->setImage('a35daad7aca921bab7a3dbb32a0ec01a.jpeg');
        $session = $manager->getRepository(Session::class)->find(16);
        $manager->persist($student3);
        $manager->flush();
        $sessionUser = new SessionUserData();
        $sessionUser->setStatus(SessionUserData::QUALIFIED);
        $sessionUser->setSession($session);
        $qualified = $manager->getRepository(Student::class)->find(32);
        $sessionUser->setUser($student3);
        $manager->persist($sessionUser);
        $manager->flush();
    }
} 
