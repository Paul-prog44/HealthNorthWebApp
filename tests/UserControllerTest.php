<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controller\UserController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UserControllerTest extends TestCase
{
   
    public function testcheckPassword(): void
    {
        $httpClientInterface = $this->createMock(HttpClientInterface::class);
        $userController = new UserController($httpClientInterface);
        
        $this->assertSame(false, $userController->checkPassword("Pasword1!"));
        $this->assertSame(false, $userController->checkPassword("thisisaverylongpassword"));
        $this->assertSame(false, $userController->checkPassword("ThisIsAVeryLongPassword"));
        $this->assertSame(false, $userController->checkPassword("ThisIsAVeryLongPassword1"));
        $this->assertSame(true, $userController->checkPassword("ThisIsAVeryLongPassword1!"));


    }
}