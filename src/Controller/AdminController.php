<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/admin', name : 'admin')]
    public function adminMenu() : Response
    {
        return $this->render('admin.html.twig');
    }

    #[Route('admin/doctors', name: 'adminDoctors')]
    public function admonDoctors() : Response
    {
        return $this->render('adminDoctors.html.twig');
    }
    
}