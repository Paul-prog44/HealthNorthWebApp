<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDoctorsController extends AbstractController
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

    public function fetchSpecialties() 
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/specialties' ,['verify_peer' => false,
            'verify_host' => false,]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
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

    public function deleteDoctorfct(int $doctorId)
    {
            $response = $this->httpClient->request(
            'DELETE', 'https://127.0.0.1:8000/api/doctors/'.$doctorId, ['verify_peer' => false,
            'verify_host' => false,]);

            $statusCode = $response->getStatusCode();
            return $statusCode;
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

    #[Route('/admin/doctors', name : 'adminDoctors')]
    public function adminDoctors() : Response
    {
        $doctorArray = $this->fetchDoctorList();
        return $this->render('adminDoctors.html.twig', ["doctorArray" => $doctorArray]);
    }

    #[Route('/admin/deleteDoctor/{doctorId}', name : 'deleteDoctor')]
    public function deleteDoctor($doctorId = null) : Response
    {
        try {
            $this->deleteDoctorfct($doctorId);
            return $this->render('confirmationCenterDeletion.html.twig');
            
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }
}