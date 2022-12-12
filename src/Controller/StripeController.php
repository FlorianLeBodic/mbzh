<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{

    #[Route('/commande/create-session/{reference}', name: 'stripe_create_session')]
    public function index(Cart $cart, ManagerRegistry $em, $reference)
    {
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';
        // remplacer par le vrai domaine pour avoir accès aux images :
        // exempe https//www.mbzh.com/uploads

        // On récupère la commande par sa référence
        $order = $em->getRepository(Order::class)->findOneBy(['reference'=> $reference]);

        // Si la référence n'existe pas, on retourne à la page de la commande
        if(!$order) {
             return $this->redirectToRoute('order');
        }

        // Remplacer le cart ici pour bien boucler sur le $order
        foreach ($order->getOrderDetails()->getValues() as $product){
            $product_object = $em->getRepository(Product::class)->findOneBy(['name' => $product->getProduct()]);
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN."/uploads".$product_object->getIllustration()],
                    ]
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                // plus besoin de faire 'unit_amount' => $order->getCarrierPrice() * 100 car fait dans easyadmin CarrierCrudController
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN],
                ]
            ],
            'quantity' => 1,
        ];

        Stripe::setApiKey('sk_test_51M0JZCGmxruVsWdOE3hVc4XjhVtB54wV1j2RBaFhKOXYV9Upz3kxOfn1kyEptT6qTh31KdX0cEBUxTdl7ftAc6K100gbE1lUWH');

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => [
                'card'
            ],
            'line_items' => [
                $products_for_stripe,
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $em->getManager()->flush();

        return $this->redirect($checkout_session->url);
    }
}
