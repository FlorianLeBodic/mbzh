<?php

namespace App\EventSubscriber;


use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private KernelInterface $appKernel;

    public function __construct(KernelInterface $appKernel){
        $this->appKernel = $appKernel;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setIllustration'],
        ];
    }

    public function setIllustration(BeforeEntityPersistedEvent $event)
    {
//        $entity = $event->getEntityInstance();
//
//        $tmp_name = $entity->getIllustration();
//
//        //Un id unique pour chaque image
//        $filename = uniqid();
//
//        // pathInfo permet de récupérer l'extension du fichier via PATHINFO_EXTENSION
//
//        $extension = pathinfo($all_files = $_FILES['Product']['name']['illustration'], PATHINFO_EXTENSION );
//
//        // Renvoie le chemin complet du projet
//        $project_dir = $this->appKernel->getProjectDir();
//
//        move_uploaded_file($tmp_name, $project_dir.'/public/uploads'.$filename.'.'.$extension);
//
//        $entity->setIllustration($filename.'.'.$extension);
    }

}