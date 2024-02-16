<?php 

namespace App\Controller;

use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminReservationController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchReservation() : array
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/reservations' ,['verify_peer' => false,
            'verify_host' => false,]);

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

    #[Route('Admin/Reservations', name : 'reservations')]
    public function adminReservations() : Response
    {
        $reservationsArray = $this->fetchReservation();
        return $this->render('admin/reservations.html.twig',['reservationsArray' => $reservationsArray]);
    }
    
    #[Route('admin/deleteReservation/{reservationId}', name : 'deleteReservation')]
    public function deleteReservation($reservationId = null) : Response
    {
        try {
            $statusCode = $this->deleteReservationApi($reservationId);
            if ($statusCode === 204 ) {
                return $this->render('confirmation/confirmationReservationDeletion.html.twig');
            }
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }
    

}