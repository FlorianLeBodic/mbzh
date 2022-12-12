<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    private ManagerRegistry $em;

    public function __construct(ManagerRegistry $em)
    {
        $this->em = $em;
    }

    #[Route('/mot-de-passe-oublie', name: 'reset_password')]
    public function index(Request $request): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('home');
        }

        if($request->get('email')){
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);

            if($user){
                // 1: enregistrer en base la demande de reset_password avec user, token, createdAt
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTime());
                $this->em->getManager()->persist($reset_password);
                $this->em->getManager()->flush();


                // 2:  Envoyer à l'utilisateur avec un lien lui permettant de mettre à jour son mot de passe
                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ],
                    UrlGeneratorInterface::ABSOLUTE_URL);

                $content = 'Bonjour ' . $user->getFirstname() . '<br/><br/>Vous avez demandé à réinitialiser votre mot de passe sur le site La Boutique Française.<br/><br/>';
                $content  .= 'Merci de bien vouloir cliquer sur le lien suivant pour <a href="'.$url.'">mettre à jour votre mot de passe</a>.';

                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), 'Réinitialisez votre mot de passe sur MBZH', $content );

                $this->addFlash('notice', 'Vous allez recevoir dans quelques instants un mail avec la procédure pour réinitialiser votre mot de passe');
            } else {
                $this->addFlash('notice', 'Cette adresse email est inconnue');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    #[Route('/modifier-mon-mot-de-passe/{token}', name: 'update_password')]
    public function update(Request $request,$token, UserPasswordHasherInterface $hasher): Response
    {
        $reset_password = $this->em->getRepository(ResetPassword::class)->findOneBy(['token' => $token]);

        if (!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }

        $now = new \DateTime();

        //Vérifier si le createdAt = now -1h
        if ($now > $reset_password->getCreatedAt()->modify('+1 hour')) {
            //modifier mon mot de passe
            $this->addFlash('notice', 'Votre demande de mot de passe a expiré. Merci de la renouveller.');
            return $this->redirectToRoute('reset_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $new_pwd = $form->get('password')->getData();

            // Encodage des mots de passe
            $password = $hasher->hashPassword($reset_password->getUser(), $new_pwd);
            $reset_password->getUser()->setPassword($password);
            // Flush en base de données
            $this->em->getManager()->flush();

            // Redirection de l'utilisateur vers la page de connection
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour.');
            return $this->redirectToRoute('app_login');
        }

        // Rendre une vue avec mmot de passe et confirmez votre mot de passe.
        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView(),
        ]);




    }
}
