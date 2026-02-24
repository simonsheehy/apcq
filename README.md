# APCQ – Association des propriétaires de cinémas du Québec

Site web institutionnel pour l'APCQ, géré par une interface d'administration Filament.

## Fonctionnalités

### Site public
- **Accueil** – Articles récents et calendrier
- **Nouvelles** – Blog avec articles (titre, extrait, contenu riche, image à la une)
- **Membres** – Annuaire des cinémas membres
- **Partenaires** – Liste des partenaires avec logos
- **Pages** – Contenu éditable (dont la page À propos)
- **Contact** – Formulaire de contact Livewire

### Administration (Filament)
- **Nouvelles** – Gestion des articles avec éditeur riche, image, date de publication
- **Membres** – Gestion des cinémas membres (nom, cinéma, coordonnées)
- **Partenaires** – Gestion des partenaires (nom, logo, URL)
- **Pages** – Gestionnaire de pages (titre, slug, contenu)
- **Menu du site** – Menu éditable (en-tête et pied de page) avec réordonnancement
- **Paramètres du site** – Pied de page (à propos, adresse, téléphone, courriel)

### Technique
- **Design** – Tailwind CSS, palette stone, couleur APCQ `#c41e3a`
- **Typographie** – Instrument Sans
- **Soft Deletes** – Sur les articles, membres et partenaires
- **Layout admin** – Formulaires en 2 colonnes (9/12 contenu + 3/12 statut, style WordPress)

## Prérequis
- PHP 8.3+
- Composer
- Node.js & npm
- MySQL/PostgreSQL/SQLite

## Installation

```bash
# Cloner le projet
cd apcq

# Dépendances PHP
composer install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données (adapter .env selon votre SGBD)
php artisan migrate --seed

# Dépendances front
npm install
npm run build

# Créer un utilisateur admin Filament
php artisan make:filament-user
```

## Développement

```bash
# Lancer le serveur, queue, logs et Vite en une commande
composer run dev

# Ou manuellement :
# php artisan serve
# npm run dev
```

Avec **Laravel Herd**, le site est disponible à `https://apcq.test`.