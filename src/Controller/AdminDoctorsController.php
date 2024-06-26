<?php

namespace App\Controller;

use Exception;
use App\Controller\DoctorController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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

    public function fetchDoctorList(int $doctorId = null) : array 
    {
        $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/doctors/'.$doctorId ,['verify_peer' => false,
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

    public function postEditDoctor(int $doctorId)
    {
        $this->httpClient->request(
            'PUT', 'https://127.0.0.1:8000/api/doctors/'.$doctorId ,[
            'verify_peer' => false,
            'verify_host' => false,
            'json' => [
                'gender' => $_POST['gender'],
                'lastName' => $_POST['lastName'],
                'firstName' => $_POST['firstName'],
                'emailAddress' => $_POST['emailAddress'],
                'centerId' => $_POST['centerId'], // TODO: fix changement centre
                'specialtyId' => $_POST['specialtyId']
            ]
        ]);
    }

    #[Route('/addDoctor', name : 'addDoctor')]
    public function createCenter() : Response //A vérifier
    {
        $centers = $this->fetchApiCentersData();
        $specialties = $this->fetchSpecialties();
        return $this->render('creation/addDoctor.html.twig', [
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
                return $this->render('confirmation/confirmationDoctorCreated.html.twig', ["doctor" => $_POST]
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
    public function adminDoctors(Request $request) : Response
    {
        $session = $request->getSession();
        if ($session->get("isAdmin")) {
            $doctorArray = $this->fetchDoctorList();
            return $this->render('admin/adminDoctors.html.twig', ["doctorArray" => $doctorArray]);
        } else {
            return $this->render('errorTemplate.html.twig', ["error" => "Cette partie est réservée aux administrateurs"]);
        }
        
    }

    #[Route('/admin/deleteDoctor/{doctorId}', name : 'deleteDoctor')]
    public function deleteDoctor($doctorId = null) : Response
    {
        try {
            if ($this->deleteDoctorfct($doctorId) === 204)
            {
                return $this->render('confirmation/confirmationDoctorDeletion.html.twig');
            }
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }

    #[Route('admin/editDoctor/{doctorId}', name : 'editDoctor')]
    public function editDoctor($doctorId = null) : Response
    {
        $centers = $this->fetchApiCentersData();
        $specialties = $this->fetchSpecialties();
        $currentDoctor = $this->fetchDoctorList($doctorId);
        return $this->render('edit/editDoctor.html.twig', [
            "centers" => $centers,
            "specialties" => $specialties,
            "currentDoctor" => $currentDoctor
        ]);
    }

    #[Route('confirmationDoctorEdition/{doctorId}', name : 'confirmationDoctorEdition')]
    public function confirmationDoctorEdition($doctorId= null) : Response
    {
        $this->postEditDoctor($doctorId);
        $currentDoctor = $this->fetchDoctorList($doctorId);
        return $this->render('confirmation/confirmationDoctorEdition.html.twig', ["currentDoctor" => $currentDoctor]);
    }

}