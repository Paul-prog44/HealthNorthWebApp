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

    public function postCenterInformation()
    {
        $this->httpClient->request(
            'POST', 'https://127.0.0.1:8000/api/centers' ,[
            'verify_peer' => false,
            'verify_host' => false,
            'json' => [
                'name' => $_POST['name'],
                'city' => $_POST['city'],
                'country' => $_POST['country'],
                'address' => $_POST['address'],
                'specialtiesArray' => $_POST['specialties']
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

    #[Route('/addCenter', name : 'addCenter')]
    public function createDoctor() : Response
    {
        $specialties = $this->fetchSpecialties();
        return $this->render('addCenter.html.twig', [
            "specialties" => $specialties
        ]);
    }

    #[Route('/confirmationCenterCreation', name : 'confirmationCenterCreation')]
    public function confirmationCenterCreation() : Response
    {

            try {
                $target_dir = "img/";
            $target_file = $target_dir . basename($_FILES["imageCenter"]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            // Check if image file is a actual image or fake image
            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES["imageCenter"]["tmp_name"]);
                if($check !== false) {
                    echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    echo "File is not an image.";
                    $uploadOk = 0;
                }
            }
            // Check if file already exists
            if (file_exists($target_file)) {
                echo("Une erreur est survenue, merci de réessayer.");
                $uploadOk = 0;
            }
            if ($_FILES["imageCenter"]["size"] > 500000) {
                echo "Sorry, your file is too large.";
                $uploadOk = 0;
            }
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
            }
            if ($uploadOk == 0) {
                echo "Votre image n'a pas pu être télechargée";
              // if everything is ok, try to upload file
              } else {
                if (move_uploaded_file($_FILES["imageCenter"]["tmp_name"], $target_file)) {
                    return $this->render('confirmationCenterCreation.html.twig', ["center" => $_POST]);
                } else {
                    return $this->render('errorTemplate.html.twig', ["error" => "Une erreur est survenue lors du téléchargement du fichier"]);
                }
              }
            } catch (Exception $e) {
                return $this->render('errorTemplate.html.twig', ["error" => $e]);
            }
    }

    #[Route('deleteCenter/{centerId}', name: 'deleteCenter')]
    public function deleteSpecificCenter($centerId = null) : Response
    {
        try {
            return $this->deleteCenter($centerId);
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }
}