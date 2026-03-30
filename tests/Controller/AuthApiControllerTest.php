<?php
namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthApiControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegisterOptionsReturnsChallenge(): void
    {
        $this->client->request('POST', '/api/auth/register/options', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => 'test@example.com']));

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('challenge', $response);
        $this->assertArrayHasKey('rp', $response);
        $this->assertArrayHasKey('user', $response);
    }

    public function testMeEndpointRequiresAuth(): void
    {
        $this->client->request('GET', '/api/auth/me');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeEndpointWithValidToken(): void
    {
        // Créer un utilisateur de test
        // L'API nécessite une config DB de test valide (sqlite memory, db etc)
        $user = new User();
        $user->setEmail('api-test@example.com');
        $user->setRoles(['ROLE_USER']);

        // Générer un JWT valide
        $token = static::getContainer()
            ->get('lexik_jwt_authentication.jwt_manager')
            ->create($user);

        $this->client->request('GET', '/api/auth/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('api-test@example.com', $response['email']);
    }
}
