<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private ManagerRegistry $em;

    public function __construct(ManagerRegistry $em)
    {
        $this->em = $em;
    }

    #[Route('/inscription', name: 'register')]
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        //Est-ce que j'aurais pas un POST
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->em->getManager();

            $search_email = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if(!$search_email){
                $password = $hasher->hashPassword($user, $user->getPassword());

                $user->setPassword($password);

                $user = $form->getData();

                $em->persist($user);
                $em->flush();
                $mail = new Mail();
                $content = "Bonjour ". $user->getFirstname() ."<br> Bienvenue sur la première boutique de luxe 100% artisanale <br>";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur la MBZH', $content);


                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte";
            }
            else{
                $notification = "L'email que vous avez renseigné exsite déjà";
            }

        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
        ]);
    }
}
