<?php
namespace App\Controller;

use App\Entity\User;
use App\Service\PasskeyAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
class AuthApiController extends AbstractController
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenManagerInterface $refreshManager
    ) {}

    #[Route('/register/options', methods: ['POST'])]
    public function registerOptions(
        Request $request,
        PasskeyAuthService $passkeyService,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $username = $data['username'] ?? null;

        if (!$email) {
            return $this->json(['error' => 'Email requis'], Response::HTTP_BAD_REQUEST);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $user = new User($email);
            if ($username) {
                $user->setUsername($username);
            }
        
            $em->persist($user);
            $em->flush();
        }

        try {
            $options = $passkeyService->getRegistrationOptions($user);
            return $this->json($options);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/register/verify', methods: ['POST'])]
    public function registerVerify(
        Request $request,
        PasskeyAuthService $passkeyService,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $credential = $data['credential'] ?? null;

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$credential) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }

        try {

            $passkeyService->verifyRegistration(json_encode($credential), $user);

            $jwt = $this->jwtManager->create($user);
            
            $refreshTokenClass = $this->refreshManager->getClass();
            $refreshToken = $refreshTokenClass::createForUserWithTtl(
                bin2hex(random_bytes(32)),
                $user, 
                2592000
            );
            $this->refreshManager->save($refreshToken);

            return $this->json([
                'success' => true,
                'token' => $jwt,
                'refresh_token' => $refreshToken->getRefreshToken(),
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/login/options', methods: ['POST'])]
    public function loginOptions(PasskeyAuthService $passkeyService): JsonResponse
    {
        try {
            return $this->json($passkeyService->getLoginOptions());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/login/verify', methods: ['POST'])]
    public function loginVerify(
        Request $request,
        PasskeyAuthService $passkeyService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $credential = $data['credential'] ?? null;

        if (!$credential) {
            return $this->json(['error' => 'Credential requis'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $passkeyService->verifyLogin(json_encode($credential));

            $jwt = $this->jwtManager->create($user);
            
            $refreshTokenClass = $this->refreshManager->getClass();
            $refreshToken = $refreshTokenClass::createForUserWithTtl(
                bin2hex(random_bytes(32)),
                $user, 
                2592000
            );
            $this->refreshManager->save($refreshToken);

            return $this->json([
                'success' => true,
                'token' => $jwt,
                'refresh_token' => $refreshToken->getRefreshToken(),
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {

        return $this->json(
            ['error' => 'Utilisez /api/token/refresh'],
            Response::HTTP_BAD_REQUEST
        );
    }

    #[Route('/me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ]);
    }

    #[Route('/logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $refreshTokenString = $data['refresh_token'] ?? null;

        if ($refreshTokenString) {
            $refreshToken = $this->refreshManager->get($refreshTokenString);
            if ($refreshToken) {
                $this->refreshManager->delete($refreshToken);
            }
        }

        return $this->json(['success' => true]);
    }
}
