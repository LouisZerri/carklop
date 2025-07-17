<?php

namespace App\Security;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailVerifier
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $router
    ) {}

    public function sendEmailConfirmation(User $user): void
    {
        $verificationUrl = $this->router->generate('app_verify_email', [
            'id' => $user->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from(new Address('no-reply@carklop.fr', 'Carklop'))
            ->to($user->getEmail())
            ->subject('Confirmez votre adresse e-mail')
            ->html("
                <div style='font-family: sans-serif; max-width: 600px; margin: auto; background: #fff; border: 1px solid #eee; border-radius: 8px; overflow: hidden;'>
                    <div style='padding: 30px;'>
                        <h2 style='color: #333;'>Bienvenue sur Carklop 👋</h2>
                        <p style='color: #555; font-size: 16px;'>Merci de vous être inscrit !</p>
                        <p style='color: #555; font-size: 16px;'>Cliquez sur le bouton ci-dessous pour confirmer votre adresse e-mail :</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$verificationUrl}' style='background-color: #e67e22; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-size: 16px;'>Confirmer mon e-mail</a>
                        </div>
                        <p style='color: #999; font-size: 12px;'>Si vous n'avez pas demandé ce message, vous pouvez ignorer cet e-mail.</p>
                    </div>
                    <div style='background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #999;'>
                        © " . date('Y') . " Carklop - Tous droits réservés
                    </div>
                </div>
            ");

        $this->mailer->send($email);
    }
}
