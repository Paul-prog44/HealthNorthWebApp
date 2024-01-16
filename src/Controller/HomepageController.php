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
            'GET', 'https://127.0.0.1:8000/api/centers',['verify_peer' => false,
            'verify_host' => false,]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;

    }

    public function fetchDoctorList() : array 
    {
        $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/doctors',['verify_peer' => false,
            'verify_host' => false,]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }

    public function fetchCenterInformation($slug) : array
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/centers/'.$slug ,['verify_peer' => false,
            'verify_host' => false,]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;

    }

    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        return $this->render('homepage.html.twig');
    }

    #[Route('/centersList')]
    public function centersList() : Response
    {
        return new Response (json_encode($this->fetchApiCentersData()));
    }


    #[Route('/centers/{slug}', name: 'centers')]
    public function centers($slug = null): Response
    {
        if ($slug) {
            $center = $this->fetchCenterInformation($slug); //recupère un centre en fonction de son id
            return $this->render('center.html.twig',
            ["center" => $center]);
        } else {
            $centers = $this->fetchApiCentersData(); //
            return $this->render('centers.html.twig',
            ["centers" => $centers]);
        }
    }

    #[Route('/create-account', name : 'connexion')]
    public function accountCreation() : Response
    {
        return $this->render('accountCreation.html.twig');
    }

    #[Route('/reservation/{slug}', name: 'reservation' )]
    public function reservation($slug = null) : Response
    {
        if ($slug) {
            $center = $this->fetchCenterInformation($slug);
            $doctors = $this->fetchDoctorList();
            return $this->render('reservation.html.twig',
            [
            "center" => $center,
            "doctors" => $doctors
            ]);
        } else {
        return $this->render('reservation.html.twig');
        }
    }
}