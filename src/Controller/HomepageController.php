<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomepageController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function postPatientInformation()
    {
        $this->httpClient->request(
            'POST', 'https://127.0.0.1:8000/api/patients' ,[
            'verify_peer' => false,
            'verify_host' => false,
            'json' => [
                'gender' => $_POST['gender'],
                'lastName' => $_POST['lastName'],
                'firstName' => $_POST['firstName'],
                'address' => $_POST['address'],
                'emailAddress' => $_POST['emailAddress'],
                'password' => $_POST['password'],
                'socialSecurity' => $_POST['socialSecurity']
            ]
        ]);
    }



    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        return $this->render('homepage.html.twig');
    }

    #[Route('/reservationConfirmation', name : 'reservationConfirmation')]
    public function reservationCreation() : Response
    {
        return $this->render('reservationConfirmation.html.twig');
    }

    #[Route('/confirmationAccountCreation', name : 'confirmationAccountCreation')]
    public function confirmationAccountCreation() :Response
    {
        try {
            $this->postPatientInformation();
            return $this->render('confirmation/confirmationAccountCreation.html.twig');
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }
    
}