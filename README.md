# Auth Native PHP

Mini-projet d'apprentissage de l'authentification en PHP natif.

## Stack

- PHP 8.2+
- MySQL
- PDO
- Sessions PHP
- Tailwind CSS pour le style
- JavaScript vanilla

## Architecture

Le projet suit une architecture MVC legere :

- `public/` contient les points d'entree HTTP et les assets publics.
- `app/` contient les controllers, models, services, middleware et views.
- `config/` contient la configuration de l'application et de la base.
- `helpers/` contient les fonctions reutilisables de validation, securite et redirection.
- `database/` contient les scripts SQL.
- `src/` contient les sources Tailwind.

## Tailwind CSS

Installer les dependances puis compiler les styles :

```powershell
npm install
npm run build:css
```

Le fichier compile est genere dans `public/css/output.css`.

## Base de donnees

Pour une nouvelle installation, executez `database/schema.sql` dans MySQL.

Si la base existait avant l'ajout des roles, executez une seule fois
`database/migrations/001_add_user_role.sql` contre `auth_native`.

Pour une base deja existante avant le systeme de validation des inscriptions,
executez ensuite `database/migrations/002_add_account_status.sql`. Les nouveaux
comptes restent en attente jusqu'a validation par un administrateur.

## Etat du projet

La premiere version fonctionnelle est disponible sur la branche `code`. Consultez `TASKS.md` pour suivre les phases et les prochaines evolutions.

## Environnement local Laragon

Le projet est prevu pour Laragon :

- Racine des projets : `C:\laragon\www`
- PHP Laragon : `C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe`
- MySQL Laragon : `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin`
- Hote MySQL : `127.0.0.1`
- Port MySQL : `3306`

Demarrer Apache et MySQL depuis Laragon, puis ouvrir temporairement :

`http://localhost/auth-native/public/`

La configuration `.env` sera utilisee pour les identifiants de la base et ne doit jamais etre versionnee. Un hote Laragon pointe directement vers `public/` sera configure lorsque les points d'entree seront implementes.

Pour verifier le runtime PHP Laragon depuis PowerShell :

```powershell
& 'C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe' --version
```

Pour verifier MySQL Laragon :

```powershell
& 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqladmin.exe' --host=127.0.0.1 --port=3306 --user=root ping
```

Pour lancer les tests smoke non destructifs :

```powershell
php tests/smoke.php
```
