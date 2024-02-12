<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use function PHPUnit\Framework\throwException;

class AdminSpecialtyController extends AbstractController 
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchSpecialty($specialtyId = null) : array
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/specialties/'.$specialtyId ,['verify_peer' => false,
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
            return $this->render('confirmation/confirmationSpecialtyDeletion.html.twig');
        }
    }

    public function editSpecialtyApi(int $specialtyId)
    {
        $this->httpClient->request(
            'PUT', 'https://127.0.0.1:8000/api/specialties/'.$specialtyId ,[
            'verify_peer' => false,
            'verify_host' => false,
            'json' => [
                'name' => $_POST['name']
            ]
        ]);
    }

    public function postSpecialty()
    {
        $this->httpClient->request(
            'POST', 'https://127.0.0.1:8000/api/specialties' ,[
                'verify_peer' => false,
                'verify_host' => false,
                'json' => [
                    'name' => $_POST['name'],
                    'centerId' => $_POST['centerId']
                ]
            ]);
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

    #[Route('admin/editSpecialty/{specialtyId}', name : 'editSpecialty')]
    public function editSpecialty($specialtyId = null) : Response
    {
        $currentSpecialty = $this->fetchSpecialty($specialtyId);
        return $this->render('edit/editSpecialty.html.twig', ['specialty' => $currentSpecialty]); 
    }

    #[Route('admin/confirmationEditSpecialty/{specialtyId}', name : 'confirmationEditSpecialty' )]
    public function confirmationEditSpecialty($specialtyId = null) : Response
    {
        $this->editSpecialtyApi($specialtyId);
        $currentSpecialty = $this->fetchSpecialty($specialtyId);
        return $this->render('confirmation/confirmationSpecialtyEdition.html.twig', ["currentSpecialty" => $currentSpecialty]);

    }

    #[Route('admin/addSpecialty', name : 'addSpecialty')]
    public function addSpecialty(CenterController $centerController) : Response
    {
        
        $centers = $centerController->fetchApiCentersData();
        // $specialties = $this->fetchSpecialties();
        return $this->render('addSpecialty.html.twig', ['centers' => $centers]);
    }

    #[Route('admin/confirmationSpecialtyCreation', name : 'confirmationSpecialtyCreation')]
    public function confirmationSpecialtyCreation() : Response
    {
        try {
            if ($_POST['name'] === "") {
                return $this->render('errorTemplate.html.twig', 
                ["error" => "Vous devez choisir un nom pour cette spécialité"]);
            }
            $this->postSpecialty();
            return $this->render('confirmation/confirmationSpecialtyCreation.html.twig');
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }

}