<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminSpecialtyController extends AbstractController 
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchSpecialty() : array
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/specialties',['verify_peer' => false,
            'verify_host' => false,]);

        $content = $response->getContent();
        $content = $response->toArray();
        return $content;
    }

    public function deleteSpecialtyApi(int $specialtyId)
    {
        $response = $this->httpClient->request(
            'DELETE', 'https://127.0.0.1:8000/api/specialties/'.$specialtyId ,
            ['verify_peer' => false,
            'verify_host' => false,]);

        $statusCode = $response->getStatusCode();
        if ($statusCode === 204 ) {
            return $this->render('confirmationSpecialtyDeletion.html.twig');
        }
    }

    #[Route ('admin/specialties', name : 'specialties')]
    public function getSpecialties() : Response
    {   
        try {
        $specialtiesArray = $this->fetchSpecialty();
        return $this->render('adminSpecialties.html.twig', ['specialtiesArray' => $specialtiesArray]);

        } catch(Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }

    #[Route('admin/deleteSpecialties/{specialtyId}', name : 'deleteSpecialty')]
    public function deleteSpecialty($specialtyId = null) : Response
    {
        try {
            return $this->deleteSpecialtyApi($specialtyId);
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }

}