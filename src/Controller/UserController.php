<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    
    private $httpClient;


    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    
    #[Route('/userAccount', name : 'userAccount')]
    public function userAccount() : Response
    {
        return $this->render('user/myAccount.html.twig');
    }

    #[Route('editAccount', name: 'editAccount')]
    public function editAccount() : Response
    {
        return $this->render('edit/editAccount.html.twig');
    }
 
}