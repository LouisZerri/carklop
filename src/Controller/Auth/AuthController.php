<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthController extends AbstractController
{

    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            $this->addFlash('error', 'Email ou mot de passe incorrect');
        }

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, ValidatorInterface $validator): Response
    {

        if ($request->isMethod('POST')) {
            $user = new User();

            // Hydratation manuelle depuis POST
            $user->setFirstname($request->request->get('firstName'));
            $user->setLastname($request->request->get('lastName'));
            $user->setEmail($request->request->get('email'));
            $user->setPhone(str_replace(' ', '', $request->request->get('tel')));

            $dob = \DateTime::createFromFormat('Y-m-d', $request->request->get('dateOfBirth'));
            if ($dob) {
                $user->setDateOfBirth($dob);
            }

            if (!$dob) {
                $this->addFlash('error', "La date de naissance est invalide.");
                return $this->redirectToRoute('app_register');
            }

            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setPicture('https://ui-avatars.com/api/?name=' . urlencode($user->getFirstname() . ' ' . $user->getLastname()));

            $plainPassword = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmPassword');

            // Vérification manuelle de confirmation
            if ($plainPassword !== $confirmPassword) {
                $this->addFlash('error', "Les mots de passe ne correspondent pas.");
                return $this->redirectToRoute('app_register');
            }

            $user->setPassword($plainPassword);

            // Validation via les @Assert
            $violations = $validator->validate($user);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
                return $this->redirectToRoute('app_register');
            }

            $terms = $request->request->get('isTermsAccepted');
            $personalUse = $request->request->get('isPersonalUseConfirmed');
            if (!$terms || !$personalUse) {
                $this->addFlash('error', "Vous devez accepter les CGU et confirmer l'utilisation personnelle.");
                return $this->redirectToRoute('app_register');
            }

            // Hachage + enregistrement
            $hashedPassword = $hasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            try {
                $em->persist($user);
                $em->flush();

                $this->emailVerifier->sendEmailConfirmation($user);

                $this->addFlash('success', 'Votre compte a été créé. Vérifiez votre boîte mail pour confirmer votre adresse.');
                return $this->redirectToRoute('app_login');
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('error', "L’inscription a échoué. Veuillez vérifier vos informations");
                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('auth/register.html.twig');
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verify(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($request->query->get('id'));

        if (!$user) {
            $this->addFlash('error', "Lien invalide ou utilisateur inconnu");
            return $this->redirectToRoute('app_login');
        }

        $user->setIsVerified(true);
        $em->flush();

        $this->addFlash('success', "Votre adresse email a bien été confirmée");
        return $this->redirectToRoute('app_login');
    }

    #[Route('/forgot-password', name: 'app_forgotpassword')]
    public function forgotPassword(Request $request, UserRepository $userRepository, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('success', 'Si un compte est associé à cette adresse, un email de réinitialisation a été envoyé.');
                return $this->redirectToRoute('app_forgotpassword');
            }

            // Génère un token et une date d'expiration
            $token = Uuid::v4();
            $user->setResetToken($token);
            $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
            $em->flush();

            // Envoie l'email
            $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            $emailMessage = (new Email())
                ->from('contact@carklop.fr')
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe')
                ->html("
                <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: auto;'>
                    <h2 style='color: #333;'>Réinitialisation de mot de passe</h2>
                    <p style='font-size: 16px; color: #555;'>
                        Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :
                    </p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='{$resetUrl}' style='background-color: #e67e22; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;'>
                            Réinitialiser mon mot de passe
                        </a>
                    </div>
                    <p style='font-size: 12px; color: #999;'>
                        Si vous n'avez pas fait cette demande, vous pouvez ignorer cet e-mail.
                    </p>
                </div>
            ");

            $mailer->send($emailMessage);

            $this->addFlash('success', 'Si un compte est associé à cette adresse, un email de réinitialisation a été envoyé');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/forgotpassword.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(string $token, Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {

        $user = $userRepository->findOneBy(['reset_token' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            $this->addFlash('error', 'Ce lien est invalide ou a expiré.');
            return $this->redirectToRoute('app_forgotpassword');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirmPassword');

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            if (
                strlen($password) < 8 ||
                !preg_match('/[A-Z]/', $password) ||
                !preg_match('/\d/', $password) ||
                !preg_match('/[^A-Za-z0-9]/', $password)
            ) {
                $this->addFlash('error', 'Le mot de passe doit contenir 8 caractères, une majuscule, un chiffre et un caractère spécial.');
                return $this->redirectToRoute('app_reset_password', ['token' => $token]);
            }

            $user->setPassword($hasher->hashPassword($user, $password));
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            $em->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/reset_password.html.twig', [
            'token' => $token
        ]);
    }
}
