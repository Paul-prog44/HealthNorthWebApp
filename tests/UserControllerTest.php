<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controller\UserController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UserControllerTest extends TestCase
{
    public function testcheckPassword(HttpClientInterface $httpClient): void
    {
        $userController = new UserController($httpClient);

        $resultOfCheckPassword = $userController->checkPassword('Alice');

        $this->assertSame(false, $resultOfCheckPassword);
    }
}