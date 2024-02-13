<?php

namespace App\Controller;


use Exception;
use App\Service\DatabaseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    
    private $httpClient;
    private $databaseService;



    public function __construct(HttpClientInterface $httpClient, DatabaseService $databaseService)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchUserInformation(int $patientId) 
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/patients/'.$patientId ,[
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }

    
    #[Route('/userAccount', name : 'userAccount')]
    public function userAccount() : Response
    {
        return $this->render('user/myAccount.html.twig');
    }

    #[Route('editAccount', name: 'editAccount')]
    public function editAccount(Request $request) : Response
    {
        $session = $request->getSession();
        $patientId = $session->get('id');
        
        //Mise à jour de la session
        try {
            return $this->render('edit/editAccount.html.twig');

        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }
    }

    #[Route('confirmationAccountEdit', name : 'confirmationAccountEdit')]
    public function confirmationAccountEdit(Request $request)
    {
        $session = $request->getSession();
        $patientId = $session->get('id');
        

        try {
          $patient = $this->fetchUserInformation($patientId);
          $currentPassword = $patient['password'];

        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }

        if ($_POST["oldPassword"] === $currentPassword)
        {
            try { $this->httpClient->request(
                'PUT', 'https://127.0.0.1:8000/api/patients/'.$patientId, [
                'verify_peer' => false,
                'verify_host' => false,
                'json' => [
                    'gender' => $_POST['genre'],
                    'firstName' => $_POST['firstName'],
                    'lastName' => $_POST['lastName'],
                    'address' => $_POST['address'],
                    'emailAddress' => $_POST['emailAddress'],
                    'socialSecurity' => $_POST['socialSecurity'],
                    'password' => $_POST['newPassword']
                ]
            ]);
            $user = $this->fetchUserInformation($patientId);

            $session->set('gender', $user['gender']);
            $session->set('last_name', $user['lastName']);
            $session->set('first_name', $user['firstName']);
            $session->set('address', $user['address']);
            $session->set('email_address', $user['emailAddress']);
            $session->set('social_security', $user['socialSecurity']);
            return $this->render('confirmation/confirmationAccountEdit.html.twig');
         
            } catch (Exception $e) 
            {
                return $this->render('errorTemplate.html.twig', ["error" => $e]);
            }
        } else 
        {
            return $this->render('errorTemplate.html.twig', ["error" => "Le mot de passe que vous avez fourni ne correspond pas."]);
        }
    }
 
}