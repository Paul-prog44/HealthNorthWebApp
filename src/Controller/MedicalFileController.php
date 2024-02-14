<?php

namespace App\Controller;


use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MedicalFileController extends AbstractController
{
    
    private $httpClient;



    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchMedicalFile(int $medicalFileId) 
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/medicalFiles/'.$medicalFileId ,[
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }

   
}