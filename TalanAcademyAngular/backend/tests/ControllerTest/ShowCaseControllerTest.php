<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 27/03/2019
 * Time: 09:35
 */

namespace App\Tests\ControllerTest;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShowCaseControllerTest extends WebTestCase
{
    public function testInscriptionPage()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testInscriptionEmailUsed()
    {
        $inputs = ['test', 'test', 'active.account@talan.com', 'talan', 'talan'];
        $crawler = $this->inscriptionTest($inputs);
        $this->assertEquals(1, $crawler->filter('.error-message-registration:contains("Cette adresse est déjà utilisée !")')->count());
    }

    private function inscriptionTest($inputs)
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('S\'inscrire')->form();

        $form['user[firstName]'] = $inputs[0];
        $form['user[lastName]'] = $inputs[1];
        $form['user[email]'] = $inputs[2];
        $form['user[password][first]'] = $inputs[3];
        $form['user[password][second]'] = $inputs[4];
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();

        return $crawler;
    }

    public function testInscriptionValidInputAndRedirect()
    {
        $inputs = ['test', 'test', 'test.inscription.redirect@talan.com', 'talan12345', 'talan12345'];
        $crawler = $this->inscriptionTest($inputs);
        $this->assertEquals(1,
            $crawler->filter('.alert.alert-success:contains("Veuillez vérifier votre boîte de réception et confirmer votre inscription")')->count());
    }

    public function testInscriptionValidInputAndSendMailConfirmation()
    {

        $client = static::createClient();
        $client->enableProfiler();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('S\'inscrire')->form();

        $form['user[firstName]'] = 'test';
        $form['user[lastName]'] = 'test';
        $form['user[email]'] = 'test.inscription.sendMail@talan.com';
        $form['user[password][first]'] = 'talan12345';
        $form['user[password][second]'] = 'talan12345';

        $crawler = $client->submit($form);
        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertSame(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();

        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('Confirmation de l\'inscription', $message->getSubject());
        $this->assertSame('talan.academy.project@gmail.com', key($message->getFrom()));
        $this->assertSame('test.inscription.sendMail@talan.com', key($message->getTo()));

    }

    public function testMailPage()
    {
        $client = static::createClient();

        $client->request('GET', '/mail');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAboutUsPage()
    {
        $client = static::createClient();

        $client->request('GET', '/about_us');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testLicencePage()
    {
        $client = static::createClient();

        $client->request('GET', '/license');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRequestForResetPwdValidAndRedirect()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('Valider')->form();

        $form['form[email]'] = 'active.account@talan.com';

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals(1,
            $crawler->filter(
                '.alert.alert-success:contains("Veuillez vérifier votre boite de réception pour poursuivre la réinitialisation de mot de passe")')->count());

    }

    public function testRequestForResetPwdValidAndSendMail()
    {
        $client = static::createClient();
        $client->enableProfiler();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('Valider')->form();

        $form['form[email]'] = 'active.account@talan.com';
        $crawler = $client->submit($form);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertSame(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();

        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('Réinitialisation de mot de passe', $message->getSubject());
        $this->assertSame('talan.academy.project@gmail.com', key($message->getFrom()));
        $this->assertSame('active.account@talan.com', key($message->getTo()));

    }

    public function testRequestForResetPwdWithInactiveAccount()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('Valider')->form();

        $form['form[email]'] = 'test.activation.3@talan.com';

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals(1,
            $crawler->filter(
                '.alert.alert-danger:contains("Votre compte n\'est pas encore actif, veuillez vérifier votre boîte de réception")')->count());

    }

    public function testRequestForResetPwdWithInvalidEmail()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/');

        $form = $crawler->selectButton('Valider')->form();

        $form['form[email]'] = 'invalid.email@talan.com';

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertEquals(1,
            $crawler->filter(
                '.alert.alert-danger:contains("Email non valide")')->count());

    }

    public function testCheckResetPwdWithValidToken()
    {
        $client = static::createClient();
        $token = "testTokenResetPasswordActiveAccount";

        $client->request('GET', "/réinitialisation-valid/$token");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCheckResetPwdWithInvalidToken()
    {
        $client = static::createClient();
        $token = "InvalidToken";

        $client->request('GET', "/réinitialisation-valid/$token");
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function providerResetPwdWithDifferenrPwd()
    {
        return [
//            [['reset_password' =>['password'=> ['first' => "admin1234k5",
//                'second' => 'admin12345']]
//
//            ],200],
            [['reset_password' =>['password'=> ['first' => "admin12345",
                'second' => 'admin12345']]

            ],302]

        ];
    }

    /**
     * @param $pwd
     * @param $expectedCode
     * @dataProvider providerResetPwdWithDifferenrPwd
     */
    public function testResetPwdWithDifferentPassword($pwd,$expectedCode)
    {
       $client = static::createClient();

        $crawler = $client->xmlHttpRequest('POST','/réinitialisation-valid/testTokenResetPasswordActiveAccount',$pwd);
        $client->setServerParameters(['HTTP_X-Requested-With' => 'XMLHttpRequest', 'REQUEST_METHOD' => 'POST']);
        $this->assertEquals($expectedCode, $client->getResponse()->getStatusCode());
    }


//    public function testResetPwdWithDifferentPassword()
//    {
//        $client = static::createClient();
//        $token = "testTokenResetPasswordActiveAccount";
//
//        $crawler = $client->request('POST', "/réinitialisation-valid/$token");
//
//        $form = $crawler->selectButton('ENREGISTRER')->form();
//
//        $form['reset_password[password][first]'] = 'test12345';
//        $form['reset_password[password][second]'] = 'test123456789';
//
//        $crawler = $client->submit($form);
//
//        $this->assertEquals(1,
//            $crawler->filter('span span span:contains("les mots de passe doivent correspondre")')->count());
//    }

//    public function testResetPwdWithInvalidPassword()
//    {
//        $client = static::createClient();
//        $token = "testTokenResetPasswordActiveAccount";
//
//        $crawler = $client->request('POST', "/réinitialisation-valid/$token");
//        $form = $crawler->selectButton('ENREGISTRER')->form();
//
//        $form['reset_password[password][first]'] = 'test';
//        $form['reset_password[password][second]'] = 'test';
//
//        $crawler = $client->submit($form);
//
//        $this->assertEquals(200, $client->getResponse()->getStatusCode());
//
//        $this->assertEquals(1,
//            $crawler->filter('span span span:contains("mot de passe doit")')->count());
//
//    }
//
//    public function testResetPwdWithInactiveAccount()
//    {
//        $client = static::createClient();
//        $token = "testTokenResetPasswordInactiveAccount";
//
//        $crawler = $client->request('GET', "/réinitialisation-valid/$token");
//
//        $crawler = $client->followRedirect();
//
//        $this->assertEquals(1,
//            $crawler->filter('.alert.alert-danger:contains("Votre compte n\'est pas encore actif, veuillez vérifier votre boîte de réception")')->count());
//    }
//
//    public function testResetPwdValid()
//    {
//        $client = static::createClient();
//
//        $token = "testTokenResetPasswordActiveAccount";
//
//        $crawler = $client->request('POST', "/réinitialisation-valid/$token");
//        $form = $crawler->selectButton('ENREGISTRER')->form();
//
//        $form['reset_password[password][first]'] = 'test12345';
//        $form['reset_password[password][second]'] = 'test12345';
//
//        $client->submit($form);
//        $client->followRedirect();
//        $this->assertEquals(200, $client->getResponse()->getStatusCode());
//    }


    public function testLicence()
    {
        $client = static::createClient();

        $client->request('GET', '/license');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testMail()
    {
        $client = static::createClient();

        $client->request('GET', '/mail');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testPedagogy()
    {
        $client = static::createClient();

        $client->request('GET', '/pedagogy');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRegistrationModality()
    {
        $client = static::createClient();

        $client->request('GET', '/modality');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCursusDetails()
    {
        $client = static::createClient();

        $client->request('GET', '/detailsCursus/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTestimonialDetails()
    {
        $client = static::createClient();

        $client->request('GET', '/detailsRim');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTestimonialDetailsJihen()
    {
        $client = static::createClient();

        $client->request('GET', '/detailsJihen');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTestimonialDetailsGhada()
    {
        $client = static::createClient();

        $client->request('GET', '/detailsGhada');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTestimonialDetailsMarwen()
    {
        $client = static::createClient();

        $client->request('GET', '/detailsMarwen');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testTestimonialDetailsSarra()
    {
        $client = static::createClient();

        $client->request('GET', '/detailsSarra');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRedirectVitrine()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/dashboard/');
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testContactUsError()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('envoyer')->form();
        $form['contact_us[name]'] = 'name';
        $form['contact_us[email]'] = 'test@gmail.com';
        $form['contact_us[message]'] = 'message';
        $crawler = $client->submit($form);
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testContactUsValid()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('envoyer')->form();
        $form['contact_us[name]'] = 'name';
        $form['contact_us[email]'] = 'test@talan.com';
        $form['contact_us[message]'] = 'message';
        $values = array_merge_recursive(
            $form->getPhpValues(),
            array(
                'g-recaptcha-response' => '03AOLTBLSb5aUhWBDV0E3hu352hsP4FfpjbMO3Mm41CLvctnmKIdSnIoF9jODVlcAes_JE6nTPsw_ki7JaubgR7MGw0lDRDSs6VTD5DGSqEWTe04HpVZjRV9eHdAo5YvGrZSrxm6FI0KRg0tQUT6qQJT49ncFSFrudUPpYyjzuqrwO1y8atzIo8y9CK_vosWHJq-K0mmgo8U05fbtwj9mKyVqlPnxTr3MKM2EsyCQMh6THLLg5V8UdpxOOow4BSfUVDrzAR2XCEyZKdtCnBVqybPN60tckS0grzCa9dSgtCeyhhNsVwWDe9ec'
            )
        );
        $crawler = $client->request('POST', '/', $values,
            $form->getPhpFiles());
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRedirectShowcase()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/dashboard/');
        $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSessionExired()
    {
        $client = static::createClient();
        $client->xmlHttpRequest('PATCH', '/admin/cursus/1/edit');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }


}
