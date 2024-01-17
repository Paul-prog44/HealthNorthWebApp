<?php

namespace App\Controller;

use Exception;
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

    public function postDoctorInformation()
    {
        $this->httpClient->request(
            'POST', 'https://127.0.0.1:8000/api/doctors' ,[
            'verify_peer' => false,
            'verify_host' => false,
            'json' => [
                'gender' => $_POST['gender'],
                'lastName' => $_POST['lastName'],
                'firstName' => $_POST['firstName'],
                'emailAddress' => $_POST['emailAddress'],
            ]
            ]);
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
            ["center" => $center]); //On passe le centre à la vue
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
            // dd( $doctors[0]["center"]["name"]. $center['name']);
            return $this->render('reservation.html.twig',
            [
            "center" => $center,
            "doctors" => $doctors
            ]);
        } else {
        return $this->render('reservation.html.twig');
        }
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
            return $this->render('confirmationAccountCreation.html.twig');
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }

    #[Route('/addDoctor', name : 'addDoctor')]
    public function createCenter() : Response
    {
        return $this->render('addDoctor.html.twig');
    }

    #[Route('/confirmationDoctorCreation', name : 'confirmationDoctorCreation')]
    public function confirmationDoctorCreation() : Response
    {
        try {
            $this->postDoctorInformation();
            return $this->render('confirmationDoctorCreated.html.twig', ["doctor" => $_POST]
        );
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }   
    }
}