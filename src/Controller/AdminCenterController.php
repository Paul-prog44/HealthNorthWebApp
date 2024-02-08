<?php

namespace App\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCenterController extends AbstractController
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
                'specialtiesArray' => $_POST['specialties'],
                'imageFileName' => $_FILES["imageCenter"]["name"]
            ]
        ]);
    }

    public function updateCenterInformation(int $centerId)
    {
        $this->httpClient->request(
            'PUT', 'https://127.0.0.1:8000/api/centers/'.$centerId ,[
            'verify_peer' => false,
            'verify_host' => false,
            'json' => [
                'name' => $_POST['name'],
                'city' => $_POST['city'],
                'country' => $_POST['country'],
                'address' => $_POST['address'],
                'specialtiesArray' => $_POST['specialties'],
                'imageFileName' => $_FILES["imageCenter"]["name"]
            ]
        ]);
    }

    public function updateCenterInformationWithoutPicture(int $centerId)
    {
        $this->httpClient->request(
            'PUT', 'https://127.0.0.1:8000/api/centers/'.$centerId ,[
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

    public function fetchSpecialties() 
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/specialties' ,['verify_peer' => false,
            'verify_host' => false,]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }


    #[Route('/admin/centers/{slug}', name: 'adminCenters')]
    public function centers($slug = null): Response
    {
        if ($slug) {
            $center = $this->fetchCenterInformation($slug); //recupère un centre en fonction de son id
            return $this->render('adminCenter.html.twig',
            ["center" => $center]); //On passe le centre à la vue
        } else {
            $centers = $this->fetchApiCentersData(); //
            return $this->render('adminCenters.html.twig',
            ["centers" => $centers]);
        }
    }

    #[Route('/admin/addCenter', name : 'addCenter')]
    public function createDoctor() : Response
    {
        $specialties = $this->fetchSpecialties();
        return $this->render('addCenter.html.twig', [
            "specialties" => $specialties
        ]);
    }

    #[Route('/admin/confirmationCenterCreation', name : 'confirmationCenterCreation')]
    public function confirmationCenterCreation() : Response
    {

            try {
                $target_dir = "img/centerImg/";
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
                        return $this->render('errorTemplate.html.twig',
                        ["error" => "Le format de cette image n'est pas accepté."]);
                        $uploadOk = 0;
                    }
                }
                // Check if file already exists
                if (file_exists($target_file)) {
                    return $this->render('errorTemplate.html.twig', ["error" => "Cette image existe déjà."]);
                    $uploadOk = 0;
                }
                if ($_FILES["imageCenter"]["size"] > 500000) {
                    return $this->render('errorTemplate.html.twig',
                    ["error" => "La taille de l'image est trop grande"]);
                    $uploadOk = 0;
                }
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" ) {
                    return $this->render('errorTemplate.html.twig', 
                    ["error" => "Seul les format d'image JPG, PNG, JPEG et GIF sont acceptés"]);
                    $uploadOk = 0;
                }
                if ($uploadOk == 0) {
                    return $this->render('errorTemplate.html.twig',
                    ["error" => "Une erreur est survenue, merci de réessayer."]);
                // if everything is ok, try to upload file
                } else {
                    if (move_uploaded_file($_FILES["imageCenter"]["tmp_name"], $target_file)) {
                        $this->postCenterInformation();
                        return $this->render('confirmationCenterCreation.html.twig', ["center" => $_POST]);
                    } else {
                        return $this->render('errorTemplate.html.twig',
                        ["error" => "Une erreur est survenue lors du téléchargement du fichier"]);
                    }
                }
            } catch (Exception $e) {
                return $this->render('errorTemplate.html.twig', ["error" => $e]);
            }
    }

    #[Route('/admin/deleteCenter/{centerId}', name: 'deleteCenter')]
    public function deleteSpecificCenter($centerId = null) : Response
    {
        try {
            return $this->deleteCenter($centerId);
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }
    
    #[Route('/admin/editCenter/{centerId}', name : 'editCenter')]
    public function editCenter($centerId = null) : Response
    {
        try {
        $currentCenter = $this->fetchCenterInformation($centerId);
        $allSpecialties = $this->fetchSpecialties();

        $currentSpecialties = []; 
        //On retourne un tableau de tableau, cette boucle sert a filtrer ce tableau
        foreach ($currentCenter['specialties'] as $specialty ) { 
            $currentSpecialties[] =  $specialty["name"];
        }
        //On récupère les informations déjà présentes pour les passer en valeur à la vue
        return $this->render('editCenter.html.twig', 
            [
            "currentCenter" => $currentCenter, 
            "specialties" => $allSpecialties,
            "currentSpecialties" => $currentSpecialties
            ]);
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }

    #[Route('/admin/confirmationCenterEdition/{centerId}', name : 'confirmationCenterEdition')]
    public function confirmationCenterEdition($centerId = null) : Response
    {
        //Si une image a été chargée
        if (!$_FILES["imageCenter"]["name"] == "") {
            try {
                $target_dir = "img/centerImg/";
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
                        return $this->render('errorTemplate.html.twig',
                        ["error" => "Le format de cette image n'est pas accepté."]);
                        $uploadOk = 0;
                    }
                }
    
                if ($_FILES["imageCenter"]["size"] > 500000) {
                    return $this->render('errorTemplate.html.twig',
                    ["error" => "La taille de l'image est trop grande"]);
                    $uploadOk = 0;
                }
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" ) {
                    return $this->render('errorTemplate.html.twig', 
                    ["error" => "Seul les format d'image JPG, PNG, JPEG et GIF sont acceptés"]);
                    $uploadOk = 0;
                }
                if ($uploadOk == 0) {
                    return $this->render('errorTemplate.html.twig',
                    ["error" => "Une erreur est survenue, merci de réessayer."]);
                // if everything is ok, try to upload file
                } else {
                    if (move_uploaded_file($_FILES["imageCenter"]["tmp_name"], $target_file)) {
                        $this->updateCenterInformation($centerId);
                        return $this->render('confirmationCenterEdition.html.twig', ["center" => $_POST]);
                    } else {
                        return $this->render('errorTemplate.html.twig',
                        ["error" => "Une erreur est survenue lors du téléchargement du fichier"]);
                    }
                }

                } catch (Exception $e) {
                    return $this->render('errorTemplate.html.twig', ["error" => $e]);
                }
        } else {
            $this->updateCenterInformationWithoutPicture($centerId);
            return $this->render('confirmationCenterEdition.html.twig', ["center" => $_POST]);
        }
    }
        
}
