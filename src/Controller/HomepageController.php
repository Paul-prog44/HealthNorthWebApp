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

    public function fetchCenterInformation(int $centerId) 
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/centers/'.$centerId ,['verify_peer' => false,
            'verify_host' => false,]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }

    public function deleteCenter(int $centerId) 
    {
            $response = $this->httpClient->request(
            'DELETE', 'https://127.0.0.1:8000/api/centers/'.$centerId ,['verify_peer' => false,
            'verify_host' => false,]);

        $statusCode = $response->getStatusCode();
        if ($statusCode === 204 ) {
            return $this->render('confirmationCenterDeletion.html.twig');
        }
    }

    public function fetchSpecialties() 
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/specialties' ,['verify_peer' => false,
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
                'centerId' => $_POST['centerId'],
                'specialtyId' => $_POST['specialtyId']
            ]
        ]);
    }
    

    

    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        return $this->render('homepage.html.twig');
    }


    #[Route('/create-account', name : 'connexion')]
    public function accountCreation() : Response
    {
        return $this->render('accountCreation.html.twig');
    }

    #[Route('/reservation/{centerId}', name: 'reservation' )]
    public function reservation($centerId = null) : Response
    {
        if ($centerId) {
            $center = $this->fetchCenterInformation($centerId);
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
        $centers = $this->fetchApiCentersData();
        $specialties = $this->fetchSpecialties();
        return $this->render('addDoctor.html.twig', [
            "centers" => $centers, 
            "specialties" => $specialties
        ]);
    }

    #[Route('/confirmationDoctorCreation', name : 'confirmationDoctorCreation')]
    public function confirmationDoctorCreation() : Response
    {
        if ($_POST["emailAddress"] == $_POST["emailAddressConfirmation"])
        {
            try {
                $this->postDoctorInformation();
                return $this->render('confirmationDoctorCreated.html.twig', ["doctor" => $_POST]
            );
            } catch (Exception $e) {
                return $this->render('errorTemplate.html.twig', ["error" => $e]);
            }
        } else 
        {
            return $this->render('missmatchEmailAddress.html.twig');
        }
    }
}