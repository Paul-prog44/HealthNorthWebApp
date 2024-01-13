<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomepageController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchApiCentersData() : array
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8001/api/centers',['verify_peer' => false,
            'verify_host' => false,]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $content = $response->toArray();

        return $content;

    }

    #[Route('/')]
    public function homepage(): Response
    {
        return $this->render('homepage.html.twig');
    }

    #[Route('/centersList')]
    public function centersList() : Response
    {
        return new Response (json_encode($this->fetchApiCentersData()));
    }


    #[Route('/centers/{slug}')]
    public function centers($slug = null): Response
    {
        if ($slug) {
            return new Response(
                'Vous avez choisi le centre '.$slug
            ); 
        } else {
            return new Response(
                'Vous n\'avez pas choisi de centre'
            );
        }
    }

    #[Route('/create-account')]
    public function accountCreation() : Response
    {
        return $this->render('accountCreation.html.twig');
    }
}