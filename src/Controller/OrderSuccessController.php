<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private ManagerRegistry $em;

    public function __construct(ManagerRegistry $em)
    {
        $this->em = $em;
    }

    #[Route('/commande/merci/{stripeSessionId}', name: 'order_success')]
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->em->getRepository(Order::class)->findOneBy(['stripeSessionId' => $stripeSessionId]);

        if(!$order || $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('home');
        }

        // Vider la session "cart"
        $cart->remove();

        if ($order->getState() == 0 ){
            // Modifier le statut isPaid de notre commande en mettant 1
            $order->setState(1);
            $this->em->getManager()->flush();
            // Envoyer un email a notre client pour lui confirmer la commande
            $mail = new Mail();
            $content = "Bonjour ". $order->getUser()->getFirstname() ."<br> Merci pour votre commande <br>";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande MBZH est bien validée', $content);
        }

        // Afficher les quelques informations de la commande de l'utilisateur

        return $this->render('order_success/index.html.twig',[
            'order' => $order
        ]);
    }
}
