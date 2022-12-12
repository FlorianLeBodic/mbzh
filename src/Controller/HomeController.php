<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\Header;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private ManagerRegistry $em;

    public function __construct(ManagerRegistry $em){
        $this->em = $em;
    }

    #[Route('/', name: 'home')]
    public function index(SessionInterface $session): Response
    {
        $products = $this->em->getRepository(Product::class)->findBy(['isBest' => 1]);
        $headers =$this->em->getRepository(Header::class)->findAll();

        // afficher la session
        //$cart = $session->get('cart');

        // virer la session
        //$cart = $session->remove('cart');

        return $this->render('home/index.html.twig',[
            'products' => $products,
            'headers' => $headers,
        ]);
    }
}
