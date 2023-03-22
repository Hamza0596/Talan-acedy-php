<?php


namespace App\EventListener;


use App\Entity\User;
use App\Event\CorrectionReportEvent;
use App\Service\Mailer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class CorrectionReportSubscriber implements EventSubscriberInterface
{
    private $templating;
    private $manager;
    private $mailer;

    public function __construct(Environment $templating, ObjectManager $manager, Mailer $mailer)
    {
        $this->templating = $templating;
        $this->manager = $manager;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
          CorrectionReportEvent::NAME => 'onCorrectionReport'
        ];
    }

    public function onCorrectionReport(CorrectionReportEvent $event)
    {
        $corrections = $event->getCorrections();
        $session = $event->getSession()->getStartDate()->format('d-m-Y');
        $cursus = $event->getSession()->getCursus()->getName();
        $correctionStudents = [];
        foreach ($corrections as $key=>$value){
            $apprenti1 = $this->manager->getRepository(User::class)->find($key);
            $apprenti2 = $this->manager->getRepository(User::class)->find($value);
            $correctionStudents[$apprenti1->getFirstName().' '.$apprenti1->getLastName()]=$apprenti2->getFirstName().' '.$apprenti2->getLastName();
        }
        $admins = $this->manager->getRepository(User::class)->findByRole('ROLE_ADMIN');
        foreach ($admins as $admin) {
            $body = //$this->templating->render('dashboard/users/emailForCorrectionReport.html.twig',
            [
                'corrections'=>$correctionStudents,
                'admin'=>$admin,
                'session' =>$session,
                'cursus' =>$cursus,
            ];
            $this->mailer->sendMail($admin->getEmail(),$cursus.' - Rapport de correction', $body);
        }


    }
}
