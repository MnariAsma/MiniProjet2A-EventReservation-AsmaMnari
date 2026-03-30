# Event Reservation Project

Ce projet est une application de gestion de réservations d'événements construite avec **Symfony 7**. Elle permet aux utilisateurs de consulter des événements, de s'y inscrire et d'utiliser des technologies modernes d'authentification comme les **Passkeys (WebAuthn)** et les **JSON Web Tokens (JWT)**.

## Fonctionnalités principales

- **Gestion des Événements** : Consultation de la liste des événements et de leurs détails.
- **Réservations** : Système d'inscription aux événements avec envoi automatique d'e-mails de confirmation.
- **Authentification Moderne** : 
    - Support des **Passkeys** pour une connexion sans mot de passe et sécurisée.
    - Utilisation de **JWT** pour l'authentification API.
    - Système de **Refresh Token** pour maintenir la session.

## Routes de l'application

### Routes Web (Frontend)

| Route | Nom | Description |
| :--- | :--- | :--- |
| `/` | `home` | Page d'accueil de l'application. |
| `/events` | `event_list` | Liste complète de tous les événements disponibles. |
| `/event/{id}` | `event_details` | Détails d'un événement spécifique (titre, date, lieu, places restantes). |
| `/event/{id}/reserve` | `event_reserve` | Formulaire et traitement de la réservation pour un événement. |
| `/login` | `app_login` | Page de connexion utilisateur. |
| `/register` | `app_register` | Page d'inscription utilisateur. |
| `/logout` | `app_logout` | Déconnexion de l'utilisateur (redirection vers login). |

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

## Installation

1. Cloner le dépôt.
2. Installer les dépendances : `composer install`.
3. Configurer le fichier `.env` (Base de données, Mailer, etc.).
4. Lancer les migrations : `php bin/console doctrine:migrations:migrate`.
5. Lancer le serveur : `symfony serve` ou `php -S localhost:8000 -t public`.

