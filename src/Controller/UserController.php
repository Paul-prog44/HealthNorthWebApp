<?php

namespace App\Controller;


use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
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

    public function fetchMedicalFile(int $medicalFileId) 
    {
            $response = $this->httpClient->request(
            'GET', 'https://127.0.0.1:8000/api/medicalFiles/'.$medicalFileId ,[
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }

    public function postAccountCreation() 
    {
            $response = $this->httpClient->request(
            'POST', 'https://127.0.0.1:8000/api/patients' ,[
            'verify_peer' => false,
            'verify_host' => false,
            'json' => [
                'gender' => $_POST['gender'],
                'lastName' => $_POST['lastName'],
                'firstname' => $_POST['firstName'],
                'address' => $_POST['address'],
                'city' => $_POST['city'],
                'emailAddress' =>$_POST['emailAddress'],
                'password' => $$_POST['password'],
                'socialSecurity' => $_POST['socialSecurity'],
                'acceptCgu' => $_POST['acceptCgu']
            ]
        ]);

        $content = $response->getContent();
        $content = $response->toArray();

        return $content;
    }
    //Vérififcation de la complexité du mdp
    public function checkPassword($password) : bool
    {
        $requiredPoints = 10;
        $length = strlen($password);
        $points_length = 0;
        $points_comp = 0;
        if($length >=10 ) { $points_length +=1 ;}
        if(preg_match("/[a-z]/", $password)) { $points_comp++ ;}
        if(preg_match("/[A-Z]/", $password)) { $points_comp+= 2 ;}
        if(preg_match("/[0-9]/", $password)) { $points_comp+=3 ;}
        if(preg_match("/\W/", $password)) { $points_comp+=4 ;}

        $results = $points_length * $points_comp;
        return ($results === $requiredPoints);

    }


    
    #[Route('/userAccount', name : 'userAccount')]
    public function userAccount(Request $request) : Response
    {
        //Récurépation de la session
        $session = $request->getSession();

        $patientId = $session->get('id');
        $user = $this->fetchUserInformation($patientId);
        $medicalFile = $this->fetchMedicalFile($user["medicalFile"]['id']);
        return $this->render('user/myAccount.html.twig', [
            'medicalFile' => $medicalFile
        ]);
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
    public function confirmationAccountEdit(Request $request) : Response
    {
        $session = $request->getSession();
        $patientId = $session->get('id');
        

        try {
          $patient = $this->fetchUserInformation($patientId);
          $currentPassword = $patient['password'];

        } catch (Exception $e) {
            return $this->render('errorTemplate.html.twig', ["error" => $e]);
        }

        if (password_verify($_POST['oldPassword'], $currentPassword) &&  $this->checkPassword($_POST['newPassword']))
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
                    'password' =>  $_POST['newPassword']
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

    #[Route('logout', name :'logout')]
    public function logout(Request $request) : Response
    {
        $session = $request->getSession();
        $session->invalidate();
        return $this->render('user/logout.html.twig');
    }

    #[Route('accountCreation', name : 'accountCreation')]
    public function accountCreation() : Response
    {
        return $this->render('user/createAccount.html.twig');
    }
    
    

    #[Route('confirmationAccountCreation', name: 'confirmationAccountCreation')]
    public function confirmationAccountCreation(Request $request): Response
    {
        if ($_POST['acceptCgu']==="on") {$_POST['acceptCgu'] = true;}
        
        if ($this->checkPassword($_POST['password']) && $_POST['password'] === $_POST['passwordConfirmation'] ){
            $user = $this->postAccountCreation();
            try{
                $session = $request->getSession();
                //Mise en session de l'utilisateur
                $session->set('isAdmin', false);
                $session->set('id', $user['id']);
                $session->set('gender', $user['gender']);
                $session->set('last_name', $user['lastName']);
                $session->set('first_name', $user['firstName']);
                $session->set('address', $user['address']);
                $session->set('email_address', $user['emailAddress']);
                $session->set('social_security', $user['socialSecurity']);
                $session->set('medical_file_id', $user['medicalFile']['id']);
                return $this->render('confirmation/confirmationAccountCreation.html.twig');
            } catch (Exception $e) {
                return $this->render('errorTemplate.html.twig', ["error" => $e]);
            }
        } else if ($this->checkPassword($_POST['password']) && $_POST['password'] !== $_POST['passwordConfirmation'] ) {
            return $this->render('error/differentPasswords.html.twig');
        } else {
            return $this->render('error/passwordUnsafe.html.twig'); 
        }
        
    }
}