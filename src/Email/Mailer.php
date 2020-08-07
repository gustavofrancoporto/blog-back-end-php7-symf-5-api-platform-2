<?php


namespace App\Email;


use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class Mailer
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendConfirmationEmail(User $user)
    {
        $body = $this->twig->render('email/confirmation.html.twig',[ 'user' => $user ]);

        $email = (new Email())
            ->from('blog@site.com')
            ->to($user->getEmail())
            ->subject('Please confirm your account!')
            ->html($body);

        $this->mailer->send($email);
    }
}