<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->addFlash('notice', 'Merci de nous avoir contacté. Notre équipe va vous répondre dans les meilleurs délais');

            $mail = new Mail();
            $gerant = "MBZH";
            $admin = "totogro123456@yopmail.com";

            $content = "Bonjour " . $gerant . ",<br/><br/>Vous avez reçu une nouvelle demande de contact:<br/>" . $form->get('prenom')->getData() . " " . $form->get('nom')->getData() . "<br/>" .    $form->get('email')->getData() . "<br/>" . "Message : " . $form->get('content')->getData() . "<br/>";
            $mail->send($admin, $gerant, 'Nouvelle demande de contact', $content);
        }

        // On peut brancher ici des API comme Zendesk API de suivi de contact





        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
