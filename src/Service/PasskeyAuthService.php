<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\WebauthnCredentialRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class PasskeyAuthService
{
    public function __construct(
        private RequestStack $requestStack,
        private WebauthnCredentialRepository $credRepo,
        private PublicKeyCredentialLoader $credentialLoader,
        private AuthenticatorAttestationResponseValidator $attestationValidator,
        private AuthenticatorAssertionResponseValidator $assertionValidator,
        private string $rpName = 'Mon Application',
        private string $rpId = 'localhost',
    ) {}

    /**
     * Génère les options pour l'enregistrement d'une passkey
     */
    public function getRegistrationOptions(User $user): array
    {
        $rpEntity = PublicKeyCredentialRpEntity::create(
            $this->rpName,
            $this->rpId
        );

        $userEntity = PublicKeyCredentialUserEntity::create(
            $user->getEmail(),
            (string)$user->getId(),
            $user->getEmail()
        );

        $challenge = random_bytes(32);

        $pubKeyCredParams = [
            PublicKeyCredentialParameters::create('public-key', -7),   // ES256
            PublicKeyCredentialParameters::create('public-key', -257), // RS256
        ];

        $authenticatorSelection = AuthenticatorSelectionCriteria::create(
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
            authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
        );

        $options = PublicKeyCredentialCreationOptions::create(
            $rpEntity,
            $userEntity,
            $challenge,
            $pubKeyCredParams,
            $authenticatorSelection,
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            [],
            60000
        );

        $this->requestStack->getSession()->set('webauthn_registration', serialize($options));
        return $options->jsonSerialize();
    }

    /**
     * Valide l'enregistrement et lie la passkey à l'utilisateur
     */
    public function verifyRegistration(string $response, User $user): void
    {
        $optionsSerialized = $this->requestStack->getSession()->get('webauthn_registration');
        $options = unserialize($optionsSerialized);

        $publicKeyCredential = $this->credentialLoader->load($response);
        $authenticatorResponse = $publicKeyCredential->response;

        if (!$authenticatorResponse instanceof AuthenticatorAttestationResponse) {
            throw new \RuntimeException('Invalid response type for registration');
        }

        $credentialSource = $this->attestationValidator->check(
            $authenticatorResponse,
            $options,
            $this->rpId
        );

        $this->credRepo->saveCredential($user, $credentialSource);
        $this->requestStack->getSession()->remove('webauthn_registration');
    }

    /**
     * Génère les options pour la connexion par passkey
     */
    public function getLoginOptions(): array
    {
        $challenge = random_bytes(32);

        $options = PublicKeyCredentialRequestOptions::create(
            $challenge,
            $this->rpId,
            [],
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            60000
        );

        $this->requestStack->getSession()->set('webauthn_login', serialize($options));
        return $options->jsonSerialize();
    }

    /**
     * Valide la connexion et retourne l'utilisateur authentifié
     */
    public function verifyLogin(string $response): User
    {
        $optionsSerialized = $this->requestStack->getSession()->get('webauthn_login');
        $options = unserialize($optionsSerialized);

        $publicKeyCredential = $this->credentialLoader->load($response);
        $authenticatorResponse = $publicKeyCredential->response;

        if (!$authenticatorResponse instanceof AuthenticatorAssertionResponse) {
            throw new \RuntimeException('Invalid response type for login');
        }

        // Find the credential source in the database
        $entity = $this->credRepo->findByCredentialId(
            $publicKeyCredential->rawId
        );

        if (!$entity) {
            throw new \RuntimeException('Passkey non reconnue');
        }

        $credentialSource = $entity->getCredentialSource();

        $this->assertionValidator->check(
            $credentialSource,
            $authenticatorResponse,
            $options,
            $this->rpId,
            $credentialSource->userHandle
        );

        $entity->touch();
        $this->credRepo->getEntityManager()->flush();
        $this->requestStack->getSession()->remove('webauthn_login');

        return $entity->getUser();
    }
}
