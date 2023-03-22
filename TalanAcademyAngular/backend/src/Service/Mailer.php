<?php
/**
 * Created by PhpStorm.
 * User: sarfaoui
 * Date: 26/03/2019
 * Time: 10:01
 */

namespace App\Service;

use Twig\Environment;

class Mailer
{
    private $mailer;
    private $mailFrom;
    private $environnemnt;
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(\Swift_Mailer $mailer, string $mailFrom, Environment $environnemnt, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->mailFrom = $mailFrom;
        $this->environnemnt = $environnemnt;
        $this->twig = $twig;
    }

    /**
     * @param $body
     */
    public function sendMail($email, $subject, $body)
    {
        $message = new \Swift_Message();
        $message->setSubject($subject)
            ->setFrom($this->mailFrom)
            ->setTo($email)
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

    public function sendMailContact($email, $subject, $body, $from)
    {
        $message = new \Swift_Message();
        $message->setSubject($subject)
            ->setFrom($from)
            ->setTo($email)
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

}
