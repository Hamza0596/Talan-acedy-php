<?php


namespace App\EventListener;


use App\Entity\SessionDayCourse;
use App\Entity\User;
use App\Event\NoInstructionsEvent;
use App\Service\Mailer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

/**
 * Class NoInstructionsSubscriber
 * @package App\EventListener
 * @codeCoverageIgnore
 */
class NoInstructionsSubscriber implements EventSubscriberInterface
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
          NoInstructionsEvent::NAME => 'onInstructionsMissed'
        ];
    }

    public function onInstructionsMissed(NoInstructionsEvent $event)
    {
        $admins = $this->manager->getRepository(User::class)->findByRole('ROLE_ADMIN');
        $validationDay = $this->manager->getRepository(SessionDayCourse::class)->find($event->getValidationDay())->getDescription();
        $module = $this->manager->getRepository(SessionDayCourse::class)->find($event->getValidationDay())->getModule()->getTitle();
        $session = $event->getSession()->getStartDate()->format('d-m-Y');
        $cursus = $event->getSession()->getCursus()->getName();
        foreach ($admins as $admin) {
            $body = //$this->templating->render('dashboard/users/emailForInstructionsMissed.html.twig', 
            [
                'admin'=>$admin,
                'module'=>$module,
                'session' =>$session,
                'cursus' =>$cursus,
                'day' => $validationDay
            ];
            $this->mailer->sendMail($admin->getEmail(), 'Instructions manquées', $body);
        }
    }
}
