<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WariController extends AbstractController
{
    /**
     * @Route("/wari", name="wari")
     */
    public function index()
    {
        return $this->render('wari/index.html.twig', [
            'controller_name' => 'WariController',
        ]);
    }
}
