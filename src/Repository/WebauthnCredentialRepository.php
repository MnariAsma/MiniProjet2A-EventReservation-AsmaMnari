<?php

namespace App\Repository;

use App\Entity\WebauthnCredential;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Webauthn\PublicKeyCredentialSource;

/**
 * @extends ServiceEntityRepository<WebauthnCredential>
 */
class WebauthnCredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebauthnCredential::class);
    }

    public function saveCredential(User $user, PublicKeyCredentialSource $source): void
    {
        $credential = new WebauthnCredential();
        $credential->setUser($user);
        $credential->setCredentialSource($source);
        $credential->setName('Passkey ' . date('Y-m-d H:i'));

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();
    }

    public function findByCredentialId(string $credentialId): ?WebauthnCredential
    {
        $credentials = $this->findAll();
        foreach ($credentials as $cred) {
            $source = $cred->getCredentialSource();
            // Utilisation d'une comparaison de chaînes binaire sécurisée
            if (hash_equals($source->publicKeyCredentialId, $credentialId)) {
                return $cred;
            }
        }
        return null;
    }
}
