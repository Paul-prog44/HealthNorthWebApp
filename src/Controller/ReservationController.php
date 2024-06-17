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

    public function postReservation($dateTimeString, $centerId) : array 
    {
        $response = $this->httpClient->request(
            'POST', 'https://127.0.0.1:8000/api/reservations/'.$centerId ,['verify_peer' => false,
            'verify_host' => false,
            'json' =>[
                'doctor' => $_POST['doctor'],
                'date' => $dateTimeString,
                'medicalFile' => $_POST['medicalFileId'],
                'comments' => $_POST['comments']
            ]
        ]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }

    public function deleteReservationApi(int $reservationId)
    {
        $response = $this->httpClient->request(
            'DELETE', 'https://127.0.0.1:8000/api/reservations/'.$reservationId ,
            ['verify_peer' => false,
            'verify_host' => false,]);

        $statusCode = $response->getStatusCode();
       return $statusCode;
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
        $dateTimeString = $_POST['interventionDate'] . ' ' . $_POST['interventionTime'];
        try {
            $this->postReservation($dateTimeString, $centerId);
        } catch (Exception $e){
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
        return $this->render('confirmation/reservationConfirmation.html.twig');
    }

    #[Route('/reservationDeletetion/{reservationId}', name:'reservationDeletetion')]
    public function reservationDeletetion($reservationId= null) : Response
    {
        try {
            $statusCode = $this->deleteReservationApi($reservationId);

            if($statusCode===204) {
                return $this->render('confirmation/confirmationReservationDeletion.html.twig');
            } else {
                return $this->render('errorTemplate.html.twig', ["error" => "Une erreur est survenu, merci de réessayer."]);
            }
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }


    }

}