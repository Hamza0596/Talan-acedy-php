<?php


namespace App\EventListener;


use App\Entity\User;
use App\Event\JokerRetraitEvent;
use App\Service\Mailer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Class JokerRetraitSubscriber
 * @package App\EventListener
 * @codeCoverageIgnore
 */

class JokerRetraitSubscriber implements EventSubscriberInterface
{
    private $templating;
    private $mailer;
    private $manager;

    public function __construct(Environment $templating, Mailer $mailer, ObjectManager $manager)
    {
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [
          JokerRetraitEvent::NAME => 'onJokerRetrait',
        ];
    }

    /**
     * @param JokerRetraitEvent $event
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @codeCoverageIgnore
     */
    public function onJokerRetrait(JokerRetraitEvent $event)
    {
        $students = $event->getStudents();
        $session = $event->getSession()->getStartDate()->format('d-m-Y');
        $cursus = $event->getSession()->getCursus()->getName();
        $day = $event->getDay()->getDescription();
        $reasonForJokerRemove = $event->getReasonForJokerRemove();
        $admins = $this->manager->getRepository(User::class)->findByRole('ROLE_ADMIN');
        foreach ($admins as $admin) {
            $subject = $cursus.' - Notification de retrait joker';
            $body = //$this->templating->render('dashboard/users/emailFor'.$reasonForJokerRemove.'.html.twig',
            [
                'students'=>$students,
                'admin'=>$admin,
                'admins'=>$admins,
                'session' =>$session,
                'cursus' =>$cursus,
                'day' => $day,
            ];
            $this->mailer->sendMail($admin->getEmail(),$subject, $body);
        }
    }

}
