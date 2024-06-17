<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CenterController extends AbstractController
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

    public function fetchSpecialties() 
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/specialties' ,['verify_peer' => false,
            'verify_host' => false,]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
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
            foreach ($centers as $center ) {
                $centerCities[] = $center['city'];
            }
            return $this->render('centers.html.twig',
            ["centers" => $centers,
            "centerCities" => $centerCities]);
        }
    }

    #[Route('/centerCity', name : 'centerCity')]
    public function centerCity() : Response
    {
        $centers = $this->fetchApiCentersData();
        foreach ($centers as $center ) {
            $centerCities[] = $center['city'];
        }
        return $this->render('centerCity.html.twig',
        ['city' =>$_POST['selectedCity'],
        "centers" => $centers,
        "centerCities" => $centerCities]);
    }
}