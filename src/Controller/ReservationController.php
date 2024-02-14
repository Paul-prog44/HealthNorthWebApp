<?php 

namespace App\Controller;

use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
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

    public function postReservation(int $centerId, DateTime $datetime) : array 
    {
        $response = $this->httpClient->request(
            'POST', 'https://127.0.0.1:8000/api/reservations',['verify_peer' => false,
            'verify_host' => false,
            'json' =>[
                'center' => $centerId,
                'doctor' => $_POST['doctor'],
                "dateTime" => $datetime //A dénifir le format
            ]
        ]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }


    #[Route('/reservation/{centerId}', name: 'reservation' )]
    public function reservation($centerId = null) : Response
    {
        if ($centerId) {
            $centerController = new CenterController($this->httpClient);
            $doctorController = new DoctorController($this->httpClient);
            $center = $centerController->fetchCenterInformation($centerId);
            $doctors = $doctorController->fetchDoctorList();
            return $this->render('reservation.html.twig',[
                'center' => $center,
                'doctors' => $doctors
            ]);
        } else {
        return $this->render('reservation.html.twig');
        }
    }

    #[Route('/reservationConfirmation/{centerId}', name : 'reservationConfirmation')]
    public function reservationCreation($centerId = null) : Response
    {
        
        return $this->render('confirmation/reservationConfirmation.html.twig');
    }

}