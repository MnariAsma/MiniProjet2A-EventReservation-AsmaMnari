# Event Reservation Project

Ce projet est une application de gestion de réservations d'événements construite avec **Symfony 7**. Elle permet aux utilisateurs de consulter des événements, de s'y inscrire et d'utiliser des technologies modernes d'authentification comme les **Passkeys (WebAuthn)** et les **JSON Web Tokens (JWT)**.

## Fonctionnalités principales

- **Gestion des Événements** : Consultation de la liste des événements et de leurs détails.
- **Réservations** : Système d'inscription aux événements avec envoi automatique d'e-mails de confirmation.
- **Authentification Moderne** : 
    - Support des **Passkeys** pour une connexion sans mot de passe et sécurisée.
    - Utilisation de **JWT** pour l'authentification API.
    - Système de **Refresh Token** pour maintenir la session.
- **Espace Administration** :
    - Tableau de bord avec statistiques (événements, réservations).
    - CRUD complet pour gérer les événements (création, édition, suppression, gestion des images).
    - Suivi des réservations par événement.

## Routes de l'application

### Routes Web (Frontend)

| Route | Nom | Description |
| :--- | :--- | :--- |
| `/` | `home` | Page d'accueil de l'application. |
| `/events` | `event_list` | Liste complète de tous les événements disponibles. |
| `/event/{id}` | `event_details` | Détails d'un événement spécifique (titre, date, lieu, places restantes). |
| `/event/{id}/reserve` | `event_reserve` | Formulaire et traitement de la réservation pour un événement. |
| `/login` | `app_login` | Page de connexion utilisateur par Passkey. |
| `/register` | `app_register` | Page d'inscription utilisateur par Passkey. |
| `/logout` | `app_logout` | Déconnexion de l'utilisateur (nettoyage de session et redirection). |

### Routes Administration (Backend)

| Route | Nom | Description |
| :--- | :--- | :--- |
| `/admin/login` | `admin_login` | Formulaire de connexion sécurisé pour les administrateurs. |
| `/admin/logout` | `admin_logout` | Déconnexion administrateur. |
| `/admin/dashboard`| `admin_dashboard`| Tableau de bord avec vue d'ensemble des événements et statistiques de réservation. |
| `/admin/event/new`| `admin_event_new`| Formulaire de création d'un nouvel événement avec upload d'image. |
| `/admin/event/{id}/edit` | `admin_event_edit` | Formulaire d'édition pour modifier les détails d'un événement. |
| `/admin/event/{id}/delete` | `admin_event_delete` | Suppression d'un événement existant. |
| `/admin/event/{id}/reservations` | `admin_event_reservations`| Liste détaillée des utilisateurs inscrits à un événement particulier. |

### Routes API (Authentification)

Toutes les routes API commencent par `/api/auth`.

| Route | Méthode | Description |
| :--- | :--- | :--- |
| `/register/options` | `POST` | Récupère les options de création de Passkey pour l'inscription. |
| `/register/verify` | `POST` | Vérifie la création de la Passkey et retourne les tokens JWT. |
| `/login/options` | `POST` | Récupère les options de défi pour la connexion par Passkey. |
| `/login/verify` | `POST` | Vérifie la signature de la Passkey et retourne les tokens JWT. |
| `/me` | `GET` | Retourne les informations de l'utilisateur actuellement authentifié. |
| `/logout` | `POST` | Invalide le Refresh Token pour clore la session API. |
| `/api/token/refresh`| `POST` | Renouvelle le token JWT à l'aide d'un Refresh Token valide. |

## Installation

1. Cloner le dépôt localement.
2. Installer les dépendances via Composer : 
   ```bash
   composer install
   ```
3. Générer les clés JWT pour l'authentification (si LexikJWTAuthenticationBundle est utilisé) :
   ```bash
   php bin/console lexik:jwt:generate-keypair
   ```
4. Configurer le fichier `.env` ou `.env.local` avec vos identifiants (Base de données, `MAILER_DSN`, etc.).
5. Créer la base de données et lancer les migrations : 
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```
6. (Optionnel) Charger des fausses données (fixtures) ou créer un compte admin manuellement si des scripts sont fournis.
7. Lancer le serveur de développement Symfony : 
   ```bash
   symfony server:start
   ```


