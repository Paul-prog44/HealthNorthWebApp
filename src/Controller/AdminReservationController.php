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

    public function fetchAllReservations() : array
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/reservations' ,['verify_peer' => false,
            'verify_host' => false,]);

        $response->getContent();
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

    public function fetchReservation(int $reservationId) {
        $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/reservations/'.$reservationId,
            ['verify_peer' => false,
            'verify_host' => false,]);
        
        $content = $response->getContent();
        $content = $response->toArray();
        return $content;
    }

    public function updateReservation(int $reservationId) {
        $response = $this->httpClient->request(
            'PUT', 'https://127.0.0.1:8000/api/reservations/'.$reservationId,
            ['verify_peer' => false,
            'verify_host' => false,
            'json' =>[
                'comments' => $_POST['comments']
            ]]);
        
        $statusCode = $response->getStatusCode();
        return $statusCode;
    }

    #[Route('admin/Reservations', name : 'reservations')]
    public function adminReservations(Request $request) : Response
    {
        $session = $request->getSession();
        $sessionData=$session->all();
        //Vérification du role
        if ($sessionData and $sessionData['isAdmin'] == true) {
            $reservationsArray = $this->fetchAllReservations();
            return $this->render('admin/reservations.html.twig',['reservationsArray' => $reservationsArray]);
        } else {
            return $this->render('errorTemplate.html.twig', ["error" => "Cette partie est réservée aux administrateurs"]);
        }
    }
    
    #[Route('admin/deleteReservation/{reservationId}', name: 'deleteReservation')]
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
    
    #[Route('admin/editReservation/{reservationId}', name: 'editReservation')]
    public function editReservation($reservationId = null)  : Response
    {
        try {
            $reservation = $this->fetchReservation($reservationId);
            return $this->render('admin/adminEditReservation.html.twig', ['reservation' => $reservation]);
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }

    #[Route('admin/editReservationConfirmation/{reservationId}', name: 'editReservationConfirmation')]
    public function editReservationConfirmation($reservationId = null) 
    {
        try {
            $statusCode = $this->updateReservation($reservationId);
            if ($statusCode === 204) {
                $reservationsArray = $this->fetchAllReservations();
                $message = "La modification a bien été effectuée";
                return $this->render('admin/reservations.html.twig',['reservationsArray' => $reservationsArray,
            'message' => $message]);
            }
        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }
}