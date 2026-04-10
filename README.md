# SYSTICKET - Système de Gestion de Tickets

Application de gestion de tickets et suivi de projets développée avec **Laravel 11**, dans le cadre de l'étape 5 à 8 du projet de migration.

## Fonctionnalités

- **Authentification** : Connexion/inscription avec session (web) et tokens Sanctum (API)
- **Gestion des tickets** : CRUD complet avec statuts (nouveau, assigné, en cours, en attente client, à valider, terminé, clos), priorités (basse, normale, haute, critique), types (inclus, facturable)
- **Gestion des projets** : Création, assignation de collaborateurs, suivi d'avancement
- **Contrats** : Gestion des heures contractuelles, tarifs, suivi de consommation
- **Saisie des temps** : Enregistrement des heures travaillées par ticket avec récapitulatif hebdomadaire
- **Validation** : Workflow de validation des tickets facturables par les clients
- **Tableau de bord** : Statistiques en temps réel, graphiques de répartition
- **Rapports** : Export des données en CSV
- **Gestion des utilisateurs** : Administration des comptes (admin uniquement)

## Rôles

| Rôle | Droits |
|------|--------|
| **Admin** | Accès total : gestion des utilisateurs, tous les projets/tickets/contrats |
| **Collaborateur** | Gestion des tickets assignés, saisie des temps, consultation des projets |
| **Client** | Consultation de ses propres tickets et projets, validation des tickets facturables |

## Prérequis

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (optionnel, pour les assets)

## Installation Rapide (Recommandé avec Laragon)

Pour la **solution la plus rapide et simple** sur Windows :

👉 **[QUICK_START_LARAGON.md](QUICK_START_LARAGON.md)** - Installation complète en 10 minutes

Si vous préférez une installation manuelle ou avez besoin de détails :

👉 **[INSTALLATION.md](INSTALLATION.md)** - Guide complet d'installation

## Setup Rapide (Manuel)

```bash
# 1. Naviguer vers le projet
cd systicket

# 2. Installer les dépendances PHP
composer install

# 3. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Créer la base de données
mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS systicket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Exécuter les migrations et charger les données
php artisan migrate
php artisan db:seed

# 6. Lancer le serveur
php artisan serve
```

L'application est accessible sur `http://localhost:8000`

**OU** exécutez le script d'installation automatisé :
```bash
SETUP.bat
```

## Comptes de démonstration

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Admin | admin@systicket.fr | password |
| Collaborateur | jean.dupont@systicket.fr | password |
| Collaborateur | marie.martin@systicket.fr | password |
| Client | pierre.leroy@client.fr | password |
| Client | sophie.bernard@client.fr | password |

## Architecture

### Structure MVC Laravel

```
systicket/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # 8 contrôleurs (Auth, Ticket, Projet, etc.)
│   │   └── Middleware/        # CheckRole middleware
│   ├── Models/                # 7 modèles Eloquent
│   └── Providers/
├── bootstrap/
│   └── app.php                # Configuration applicative
├── config/                    # Fichiers de configuration Laravel
├── database/
│   ├── migrations/            # 10 migrations
│   └── seeders/               # Données de démonstration
├── public/
│   ├── css/                   # Feuilles de style
│   ├── js/                    # JavaScript (app.js + helpers)
│   └── index.php              # Point d'entrée
├── resources/
│   └── views/                 # Vues Blade
│       ├── layouts/           # Layout principal
│       ├── components/        # Header, sidebars
│       └── pages/             # Pages de l'application
└── routes/
    ├── web.php                # Routes web (session)
    └── api.php                # Routes API (Sanctum)
```

### Modèles et Relations

- **User** : hasMany(Ticket, Temps, Commentaire), belongsToMany(Projet, Ticket)
- **Projet** : belongsTo(User client, User manager), belongsToMany(User), hasMany(Ticket, Contrat)
- **Ticket** : belongsTo(Projet, User client, User creator), belongsToMany(User assignees), hasMany(Temps, Commentaire, Validation)
- **Contrat** : belongsTo(Projet, User client)
- **Temps** : belongsTo(User, Ticket)
- **Commentaire** : belongsTo(Ticket, User)
- **Validation** : belongsTo(Ticket, User)

## API REST

Toutes les routes API sont préfixées par `/api/` et protégées par Laravel Sanctum.

### Authentification

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/login` | Connexion (retourne token) |
| POST | `/api/register` | Inscription |
| POST | `/api/logout` | Déconnexion |
| GET | `/api/user` | Utilisateur connecté |

### Tickets

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/tickets` | Liste des tickets (filtres: status, priority, project_id, search) |
| GET | `/api/tickets/{id}` | Détail d'un ticket |
| POST | `/api/tickets` | Créer un ticket |
| PUT | `/api/tickets/{id}` | Modifier un ticket |
| DELETE | `/api/tickets/{id}` | Supprimer un ticket |
| PUT | `/api/tickets/{id}/status` | Changer le statut |
| POST | `/api/tickets/{id}/comments` | Ajouter un commentaire |
| POST | `/api/tickets/{id}/assign` | Assigner des collaborateurs |

### Projets

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/projets` | Liste des projets |
| GET | `/api/projets/{id}` | Détail d'un projet |
| POST | `/api/projets` | Créer un projet |
| PUT | `/api/projets/{id}` | Modifier un projet |
| DELETE | `/api/projets/{id}` | Supprimer un projet |

### Contrats

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/contrats` | Liste des contrats |
| GET | `/api/contrats/{id}` | Détail d'un contrat |
| POST | `/api/contrats` | Créer un contrat |
| PUT | `/api/contrats/{id}` | Modifier un contrat |
| DELETE | `/api/contrats/{id}` | Supprimer un contrat |

### Temps

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/temps` | Liste des saisies de temps |
| POST | `/api/temps` | Ajouter une saisie |
| PUT | `/api/temps/{id}` | Modifier une saisie |
| DELETE | `/api/temps/{id}` | Supprimer une saisie |
| GET | `/api/temps/week-summary` | Récapitulatif hebdomadaire |

### Validations

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/validations` | Liste des tickets à valider |
| POST | `/api/validations/validate` | Valider/refuser un ticket |

### Dashboard

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/dashboard/stats` | Statistiques du tableau de bord |

### Utilisateurs (admin)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/users` | Liste des utilisateurs |
| GET | `/api/users/{id}` | Détail d'un utilisateur |
| POST | `/api/users` | Créer un utilisateur |
| PUT | `/api/users/{id}` | Modifier un utilisateur |
| DELETE | `/api/users/{id}` | Supprimer un utilisateur |
| GET | `/api/users/collaborateurs` | Liste des collaborateurs |
| GET | `/api/users/clients` | Liste des clients |

## Sécurité

- **Authentification** : Laravel Sanctum (tokens API + session web)
- **Autorisation** : Middleware `CheckRole` basé sur les rôles (admin, collaborateur, client)
- **CSRF** : Protection automatique via middleware Laravel
- **Validation** : Validation côté serveur sur tous les formulaires
- **Mots de passe** : Hashés avec Bcrypt via `Hash::make()`
- **XSS** : Échappement automatique Blade (`{{ }}`)
- **SQL Injection** : Protection via Eloquent ORM et Query Builder

## Technologies

- **Backend** : Laravel 11, PHP 8.2+
- **Base de données** : MySQL avec Eloquent ORM
- **Authentification API** : Laravel Sanctum
- **Frontend** : Blade Templates, JavaScript vanilla (fetch API)
- **CSS** : CSS personnalisé (style.css, roles.css)
