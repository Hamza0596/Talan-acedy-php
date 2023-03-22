<?php


namespace App\Service;


use App\Entity\Student;
use App\Entity\User;
use App\Form\ContactUsType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserService extends AbstractController
{
    const USER = 'user';
    const CODE = 'code';
    const MESSAGE = 'message';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;

    }

    function inscription($request)
    {

        $student = new Student();
        $form = $this->createForm(UserType::class, $student);
        $form->submit($request->request->all());
        if ($form->isSubmitted()) {
            $lastName = strtoupper($student->getLastName());
            $firstName = ucfirst(strtolower($student->getFirstName()));
            $student->setLastName($lastName);
            $student->setFirstName($firstName);
            $student->setEmail($student->getEmail());
            $password = password_hash($form->getData()->getPassword(), PASSWORD_BCRYPT);
            $student->setPassword($password);
            $student->setRoles([User::ROLE_INSCRIT]);
            $student->setRegistrationDate(new \DateTime());
            $student->setIsActivated(false);
            $this->entityManager->persist($student);
            $this->entityManager->flush();
            return [
                self::USER => $student,
                self::CODE => 1,
                self::MESSAGE => "Utilisateur ajouté avec succès !"
            ];
        }
        return [
            self::USER => $student,
            self::CODE => 0,
            self::MESSAGE => 'un problème est survenu !'
        ];
    }

    function contactUs($request, $mailer)
    {

        $secret = $_ENV['GOOGLE_RECAPTCHA_SECRET'];
        $recaptcha = new ReCaptcha($secret);
        $contactUsForm = $this->createForm(ContactUsType::class);
        $contactUsForm->handleRequest($request);
        $resp = $recaptcha->verify($request->request->get('recaptcha'), $request->getClientIp());
        if (!$resp->isSuccess()) {
            return [
                self::CODE => 0,
                self::MESSAGE => "Veuillez prouver que vous n\'êtes pas un robot !"
            ];
        } else {
            $name = $request->request->all()['name'];
            $email = $request->request->all()['email'];
            $bodyMessage = $request->request->all()['message'];
            $subject = 'Nous écrire, de la part de' . $name;
            $mailer->sendMailContact('talan.academy.project@gmail.com', $subject, $bodyMessage, $email);
            $mailer->sendMail($email, 'Message envoyé à Talan Academy', $bodyMessage);
            return [
                self::CODE => 1,
                self::MESSAGE => "Message envoyé  avec succès !"
            ];
        }
    }

}
