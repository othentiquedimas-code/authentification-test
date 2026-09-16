# Taches

## Phase 0 - Audit Auth-Native

[x] Inspecter l'architecture existante
[x] Identifier la table `users`
[x] Identifier le systeme de roles, sessions et middleware
[x] Identifier la connexion PDO, le fichier de configuration et le Tailwind
[x] Identifier le contexte Laragon et la configuration Git
[x] Proposer l'adaptation vers UniSoutenance

## Phase 1 - Schema de base de donnees

[x] Adapter le schema `users` pour inclure le role
[x] Creer les tables `filiers`, `students`, `teachers`, `rooms`, `defenses` et `defense_jury`
[x] Ajouter les contraintes de relation et les cles etrangeres
[x] Valider la structure relationnelle pour la gestion des soutenances

## Phase 2 - Filiere

[x] Creer le CRUD des filieres
[x] Valider le code unique
[x] Ajouter les filtres et messages de validation

## Phase 3 - Enseignants

[x] Creer le CRUD des enseignants
[x] Valider les donnees de base
[x] Ajouter la recherche et la gestion des roles de jury

## Phase 4 - Etudiants

[x] Creer le CRUD des etudiants
[x] Valider le matricule unique
[x] Relier chaque etudiant a une filiere

## Phase 5 - Salles

[x] Creer le CRUD des salles
[x] Valider la capacite et les donnees de base

## Phase 6 - Soutenances

[x] Creer le CRUD des soutenances
[x] Valider les donnees d'etudiant, encadreur, salle et statut
[x] Gerer les contraintes metier de base

## Phase 7 - Jurys

[x] Creer la gestion des jurys par soutenance
[x] Ajouter les roles president / examinateur / rapporteur

## Phase 8 - Dashboard administrateur

[x] Ajouter le dashboard admin avec statistiques et prochaines soutenances

## Phase 9 - Dashboard enseignant

[x] Ajouter le dashboard enseignant
[x] Afficher les soutenances, etudiants encadres et jurys

## Phase 10 - Dashboard etudiant

[x] Ajouter le dashboard etudiant
[x] Afficher les informations de la soutenance et l'encadreur

## Phase 11 - Calendrier et filtres

[x] Creer la page du calendrier
[x] Ajouter les filtres par date, filiere, salle et statut

## Phase 12 - Permissions et securite

[x] Controler les roles cote serveur
[x] Verifier les permissions admin / enseignant / etudiant
[x] Renforcer la securite globale
[x] Ajouter la validation admin des comptes inscrits

## Phase 13 - Interface responsive

[x] Finaliser la sidebar, navbar, cards et tables
[x] Harmoniser l'identite visuelle UniSoutenance

## Phase 14 - Tests

[x] Verifier les flux principaux de connexion, deconnexion et redirection
[x] Valider les permissions et les erreurs serveur
[x] Valider les requetes PDO, le lint PHP et la compilation Tailwind
[x] Ajouter une suite de smoke tests automatises

## Phase 15 - Nettoyage et documentation

[x] Finaliser la documentation de la base et des migrations
[x] Nettoyer les fichiers et corriger les routes principales
[x] Verifier le projet avant livraison
[x] Ajouter une suite de smoke tests automatises
