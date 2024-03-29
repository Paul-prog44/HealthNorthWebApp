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
        return $this->render('admin/admin.html.twig');
    }

    #[Route('admin/Doctors', name: 'adminDoctors')]
    public function adminDoctors() : Response
    {
        return $this->render('admin/adminDoctors.html.twig');
    }
    
}