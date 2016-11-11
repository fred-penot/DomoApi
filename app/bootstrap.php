<?php

if (strstr(strtolower(getenv('APPLICATION_ENV')), "dev") !== false) {
    $env = "dev";
} elseif (strstr(strtolower(getenv('APPLICATION_ENV')), "prod") !== false) {
    $env = "prod";
} else {
    die("Veuillez indiquer une valeur <development> ou <production> pour APPLICATION_ENV");
}
// chargement des paramètres de l'application en fonction de l'environnement
require __DIR__ . '/parameters_' . $env . '.php';
// chargement des registres de l'application
require __DIR__ . '/register.php';
// chargement des fonctions middleware de l'application
require __DIR__ . '/middleware.php';
// chargement des routes de l'application
require __DIR__ . '/routing.php';
// chargement des services de l'application
require __DIR__ . '/services.php';

