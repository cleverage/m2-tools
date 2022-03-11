CleverAge_Tools
=========

## Description

Ce module fournit des outils génériques pour les instances de Magento 2, déployables sur tous les environnements.


## Fonctionnalités

- Commande `bin/magento cleverage:tools:setup:configphpgen`
    - Génère ou met à jour le fichier `app/etc/config.php` de Magento 2 avec les modules disponibles localement.
    - Utilisé par les déploiements Capistrano afin de s'assurer que les modules présents dans ce fichier sont bien déployés sur l'instance (indispensable depuis que la commande `setup:upgrade` a été remplacée par `setup:db-schema:upgrade` et `setup:db-data:upgrade` exécutées uniquement sur le frontend primaire).

- Commande `bin/magento cleverage:tools:setup:di:compile_safe`
    - Identique à la commande standard `setup:di:compile` excepté que toute erreur de compilation entraînera un échec et retournera un code d'erreur non-nul (à utiliser lors des déploiements donc)

- Classe de debug `CleverAge\Tools\Debug`
    - Fournit des méthodes de debug optimisés pour Magento 2 (support des DataObject, affichage mémoire consommée, dump récursif limité, contexte, etc.) :
        - `dump ($value, $maxDepth = 8, $file = false, $label = '', $addContext = true)`
        - `vardump ($value, $maxDepth = 8, $file = false, $label = '', $addContext = true)`
        - `debugBacktrace ($file = false)`

- Commande `bin/magento cleverage:tools:sql:run`
    - Exécuter une requête SQL sur la base Magento

- Commande `bin/magento cleverage:tools:cronjob:run`
    - Exécuter un cron job unique

- Bannière de version en bas de page
    - Affiche la version, la révision et la date de déploiement (via les fichiers générés automatiquement par Capistrano)
    - Déposer des fichiers REVISION et VERSION à la racine des sources projet pour le contenu à afficher (format libre; si la fonction est activée ces fichiers sont obligatoires)
        - exemple REVISION : bcbd853 (numéro de commit)
        - exemple VERSION : 1.1.4-RC13
    - Désactivable par `core_config_data` (par défaut : activé sur le backend, désactivé sur le frontend)

- Message d'avertissement lors de la tentative de réindexation d'un indexer déjà flaggé *WORKING* (ignoré silencieusement par Magento sinon)

## Installation

```
composer require cleverage/m2-tools
```
