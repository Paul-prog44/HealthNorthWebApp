<?php

namespace App\Controller;

use Exception;
use App\Service\DatabaseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Authentication extends AbstractController
{
    
    private $httpClient;
    private $databaseService;


    public function __construct(HttpClientInterface $httpClient, DatabaseService $databaseService)
    {
        $this->httpClient = $httpClient;
        $this->databaseService = $databaseService;
    }


    #[Route('/connexion', name : 'connexion')]
    public function accountCreation() : Response
    {
        return $this->render('user/connexion.html.twig');
    }


    #[Route('/loggin', name : 'loggin')]
    public function loggin(Request $request) 
    {
        $session = $request->getSession();
        $emailAddress = $_POST['emailAddress'];
        $password =  $_POST['password'];


        $query = 'SELECT * FROM north_health.patient WHERE email_address = :email'; //WHERE email_address = jsmith@gmail.com
        $params = ['email' => $emailAddress];
        try {

            $result = $this->databaseService->executeQuery($query, $params);

            if ($_POST['emailAddress']=== "" || $_POST['emailAddress'] === null || $result === [] ) {
                return $this->render('user/connexionFailed.html.twig');
            }
            else if ($result[0]['password'] === $password) {
                //Mise en session de l'utilisateur

                $session->set('isLogged', true);
                $session->set('id', $result[0]['id']);
                $session->set('medical_file_id', $result[0]['medical_file_id']);
                $session->set('gender', $result[0]['gender']);
                $session->set('last_name', $result[0]['last_name']);
                $session->set('first_name', $result[0]['first_name']);
                $session->set('address', $result[0]['address']);
                $session->set('email_address', $result[0]['email_address']);
                $session->set('password', $result[0]['password']);
                $session->set('social_security', $result[0]['social_security']);

                return $this->render('user/connexionSuccess.html.twig');
            } else  {
                return $this->render('user/connexionFailed.html.twig');
            }
        } catch (Exception $e) 
        {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }    
    }

}