<?php

namespace App\Controller;


use App\DataFixtures\AppFixturesTest;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class StudentControllerTest extends WebTestCase
{
    private $client = null;
    private $user;
    private $student = null;
    private $manager;
    private $route;
    private $filesystem;

    public function setUp()
    {
        $kernel = self::bootKernel();
        $this->filesystem = $kernel->getContainer()->get('filesystem');
        $manager = $kernel->getContainer()->get('doctrine')->getManager()->flush();
        $this->route = $kernel->getProjectDir();
        $this->client = static::createClient();
        $this->student =  $this->client;
        $this->logIn($this->client);
    }

    private function logIn($client)
    {
        $session = $client->getContainer()->get('session');
        $this->user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('test.activationprofile.1@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($this->user, $firewall, $this->user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
    private function logInStudentWithoutCandidature()
    {

        $session = $this->student->getContainer()->get('session');
        $this->user = $this->student->getContainer()->get('doctrine')->getRepository(User::class)->findOneByEmail('active.account@talan.com');
        $firewall = 'main';
        $token = new PostAuthenticationGuardToken($this->user, $firewall, $this->user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->student->getCookieJar()->set($cookie);
    }


    public function testGetLevel()
    {
        $this->client->xmlHttpRequest('POST', '/candidate/level');
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testListCursusBeforeCandidature()
    {
        $this->client->request('GET', 'candidate/cursus-list');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testListCrsus()
    {
        $this->client->xmlHttpRequest('GET', 'candidate/cursus-list');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testCandidature()
    {
        $crawler = $this->client->request('GET', 'candidate/Candidatures');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testEditProfileStudentError()
    {
        $formStudent2 = ['student' => [
            'firstName' => "m",
            'lastName' => 'whd',
        ]];
        $this->client->xmlHttpRequest('POST', '/candidate/profile', $formStudent2);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testGetProfileStudent()
    {
        $this->client->request('POST', '/candidate/profile');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEditEmailFailed()
    {
        $formEmail3 = ['user_edit_email' => ['email' => "test.activationprofile.1@talan.com"]];
        $this->client->xmlHttpRequest('POST', '/candidate/edit-email', $formEmail3);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditEmailValid()
    {
        $formEmail3 = ['user_edit_email' => ['email' => "test.activationn.3@talann.com"]];
        $this->client->xmlHttpRequest('POST', '/candidate/edit-email', $formEmail3);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testEditEmailExist()
    {
        $formEmail = ['user_edit_email' => ['email' => "admin@talan.com"]];
        $this->client->xmlHttpRequest('POST', '/candidate/edit-email', $formEmail);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditEmailSame()
    {
        $formEmail2 = ['user_edit_email' => ['email' => "test.activation.1@talan.com"]];
        $this->client->xmlHttpRequest('POST', '/candidate/edit-email', $formEmail2);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditEmailErorrFormat()
    {
        $formEmail1 = ['user_edit_email' => ['email' => "format invalide"]];

        $this->client->xmlHttpRequest('POST', '/candidate/edit-email', $formEmail1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditEmailErorrRequest()
    {
        $formEmail1 = ['user_edit_email' => ['email' => "format invalide"]];

        $this->client->request('POST', 'candidate/edit-email', $formEmail1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function testEditPassword()
    {
        $formPassword = ['user_edit_password' => [
            'oldPassword' => "talan12345",
            'password' => [
                'first' => 'talan123456',
                'second' => 'talan123456']
        ]];

        $this->client->xmlHttpRequest('POST', 'candidate/edit-password', $formPassword);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testEditPasswordError()
    {
        $formPassword1 = ['user_edit_password' => [
            'oldPassword' => "test12h3456",
            'password[first]' => 'talan1234567',
            'password[second]' => 'talan1234567']];
        $this->client->xmlHttpRequest('POST', '/candidate/edit-password', $formPassword1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testEditPasswordErrorRequest()
    {
        $formPassword1 = ['user_edit_password' => [
            'oldPassword' => "test12h3456",
            'password[first]' => 'talan1234567',
            'password[second]' => 'talan1234567']];
        $this->client->request('POST', '/candidate/edit-password', $formPassword1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }


    public function testConfirmationUpdateEmailError()
    {
        $this->client->request('GET', '/candidate/update-email/12345');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }


    public function testConfirmationUpdateEmail()
    {
        $this->client->request('GET', '/candidate/update-email/testTokenActivation');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }


    public function testConfirmationUpdateEmailAdmin()
    {
        $this->user->setRoles([User::ROLE_ADMIN]);
        $this->manager;
        $this->client->request('GET', '/candidate/update-email/25414');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testConfirmationUpdateEmailMentor()
    {
        $this->user->setRoles([User::ROLE_MENTOR]);
        $this->manager;
        $this->client->request('GET', '/candidate/update-email/25414');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }


    public function testGetImageProfile()
    {
        $this->client->request('GET', '/candidate/profile');
        $this->assertFileExists($this->route . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_upload' . DIRECTORY_SEPARATOR . 'image_user' . DIRECTORY_SEPARATOR . $this->user->getImage());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetImageProfileError()
    {
        $this->client->request('GET', '/candidate/profile');
        $this->assertFileNotExists($this->route . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_upload/image_user' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->user->getImage());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEditProfileStudent()
    {
        $formStudent1 = ['student' => [
            'firstName' => "mhdi",
            'lastName' => 'whdd',
            'tel' => '53875208',
            'city' => 'Tunis',
        ]];
        $this->client->xmlHttpRequest('POST', '/candidate/profile', $formStudent1);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testAddImageFailed()
    {
        $this->client->request('GET', 'candidate/profile');
        $photo = new UploadedFile(
            $this->route . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_upload' . DIRECTORY_SEPARATOR . 'image_user' . DIRECTORY_SEPARATOR . 'test_image.jpg',
            'placeholder_image.jpg',
            'image/jpeg',
            null
        );
        $formEditphoto = ['image_user' => ['image' => $photo]];
        $this->client->xmlHttpRequest('POST', '/candidate/edit-image', $formEditphoto);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testCursusDetail()
    {
        $this->client->request('GET', '/candidate/cursus-detail/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }
    public function testCursusDetailWithoutCandidature()
    {
        $this->logInStudentWithoutCandidature();
        $this->student->request('GET', '/candidate/cursus-detail/1');
        $this->assertEquals(200, $this->student->getResponse()->getStatusCode());

    }

    public function testAddCandidatePreparcoursFail(){
        $this->client->request('POST', '/candidate/add-candidate-preparcours',['candidature'=>12]);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
    public function testAddCandidatePreparcours(){

        $this->client->xmlHttpRequest('POST', '/candidate/add-candidate-preparcours',['candidature'=>12]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSaveRepoGit(){

        $formGit = ['candidate_preparcours_work' => [
            'repoGit' => 'https://www.git.com',
        ]];
        $this->client->xmlHttpRequest('POST', '/candidate/saveRepoGit', $formGit);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testSaveRepoGitInvalid(){

        $formGit = ['candidate_preparcours_work' => [
            'repoGit' => 'https://www.git.com',
        ]];
        $this->client->request('POST', '/candidate/saveRepoGit', $formGit);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
    public function testViewPreparcoursPdf(){
        $this->client->request('GET', '/candidate/download-preparcours/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testApplyApplicationWithPreparcoursNull()
    {
        $this->client->request('GET', '/candidate/apply_application/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testApplyApplication()
    {
        $this->client->request('GET', '/candidate/apply_application/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testApplyApplicationWithCursusNull()
    {
        $this->client->request('GET', '/candidate/apply_application');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }




    public function testApplyApplicationSubmitFormInformationWithoutCity()
    {
        $formInformation = ['candidate_personal_information' => [
            'birthday' => '17-10-1987',
            'tel' => 55223344,
        ]];
        $this->client->xmlHttpRequest('POST', '/candidate/apply_application/1', $formInformation);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testApplyApplicationSubmitFormFormation()
    {
        $this->filesystem->copy('./public/file_upload/cv-upload-test/cv_test.pdf', './public/file_test/cv_test.pdf');
        $cv = new UploadedFile(
        $this->route . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file_test' . DIRECTORY_SEPARATOR . 'cv_test.pdf',
        'cv_test.pdf',
        'application/pdf',
        null,
        true
    );
        $formFormation = ['candidate_formation' => [
            'grades' => 'Bac+5 (Ingénieur, Master2)',
            'degree' => 'Ingénieur Info',
            'currentSituation' => 'Salarié(e)',
            'itExperience' => true,
            'cv' => $cv,
        ]];
        $this->client->xmlHttpRequest('POST', '/candidate/apply_application/1', $formFormation);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testApplyCursusInvalidExceptInformation()
    {
        $this->client->xmlHttpRequest('PATCH', '/candidate/applyCursus/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testApplyApplicationSubmitFormInformationAll()
    {
        $formInformation = ['candidate_personal_information' => [
            'firstName' => 'Slim',
            'lastName' => 'Arfaoui',
            'birthday' => '17-10-1987',
            'tel' => 55223344,
            'city' => 'Sousse',
        ]];
        $this->client->xmlHttpRequest('POST', '/candidate/apply_application/1', $formInformation);
        $this->client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testApplyCursusValid()
    {
        $this->client->xmlHttpRequest('PATCH', '/candidate/applyCursus/1');
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
    }

    public function testApplyCursusInvalidRequest()
    {
        $this->client->request('PATCH', '/candidate/applyCursus/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testGetMoreCursusValid()
    {
        $this->client->xmlHttpRequest('GET', '/candidate/moreCursus/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetMoreCursusInvalid()
    {
        $this->client->request('GET', '/candidate/moreCursus/1');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testRedirectDashboard()
    {
        $this->client->request('GET', '/admin/dashboard/');
        $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }




}
